<?php

namespace App\Actions;

use App\Contracts\Tagger;
use App\Events\NotamProcessingEvent;
use App\OpenAI\Prompt;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OpenAITagger implements Tagger
{
    protected Collection $notams;

    protected ?string $channelName;

    protected Collection $chunkedNotams;

    protected Gpt3Tokenizer $tokenizer;

    public static function tag(Collection $notams, ?string $channelName = null): Collection
    {
        return (new self())->process($notams, $channelName);
    }

    public function process(Collection $notams, ?string $channelName = null): Collection
    {
        $this->notams = $notams;
        $this->channelName = $channelName;

        $this->optimallyGroupNotams();

        $taggedData = $this->tagChunks();

        //Need to attach this data to the original notam source
        return $this->notams
            ->map(fn (array $notam) => $this->addTaggedData($notam, $taggedData))
            ->groupBy('location');
    }

    /**
     * Sending one notam per request is very inefficient. But we are limited to approx 4000 tokens per
     * request, so we have to work out how many notams we can send in each batch to the API.
     *
     * To do this is a simple way, we use a library to calculate how many tokens the prompt is, and then
     * we keep adding another notam and see how many tokens we've used up. If the next notam to be added
     * would go over our maximum token number we create a new batch.
     *
     * This strikes a good balance between simple and efficient.
     */
    private function optimallyGroupNotams(): void
    {
        //If you need to test API by sending 1 notam at a time:
        //$this->chunkedNotams = $this->notams
        //    ->chunk(1);
        //return;
        $this->chunkedNotams = collect();

        $totalTokens = 0;
        $promptTokens = $this->getTokenizer()->count(collect(Prompt::get())->pluck('content')->implode(' '));
        $maxTokensPerRequest = 4000 - $promptTokens;
        $currentGroup = collect();

        $this->notams
            ->each(function (array $notam) use (&$currentGroup, &$totalTokens, $maxTokensPerRequest) {

                //The notams from the icao source annoying have a message field for
                //some airports but not all of them. Fall back to the full notam if it doesn't exist
                $text = $notam['message'] ?? $notam['all'];
                $notamTokenCount = $this->getTokenizer()->count("{$notam['key']}': ".$text) + 90; //Add estimate reply token here.

                //Adding this notam will push us over our maximum token allowance for this group.
                //Therefore, this batch is full, push it onto array.
                if ($totalTokens + $notamTokenCount > $maxTokensPerRequest) {
                    $this->chunkedNotams->push($currentGroup);
                    //Reset the counters for the start of the next batch
                    $currentGroup = collect();
                    $totalTokens = 0;
                }

                //Otherwise we still have more room in this group for this notam.
                $currentGroup->push($notam);
                $totalTokens += $notamTokenCount;
            });

        // Add the last group if it contains any strings
        if ($currentGroup->isNotEmpty()) {
            $this->chunkedNotams->push($currentGroup);
        }
    }

    protected function getTokenizer(): Gpt3Tokenizer
    {
        return $this->tokenizer ??= new Gpt3Tokenizer(new Gpt3TokenizerConfig());
    }

    /**
     * Let's send each chunk of notams to the openapi to be processed and tagged.
     */
//    protected function tagAllNotams(): Collection
//    {
//        $this->tagChunks()
//        return $this->chunkedNotams
//            //ChunkNumber is zero indexed, for humans lets make it indexed from 1
//            ->map(fn (Collection $notamChunk, $chunkNumber) => $this->rateLimitTaggingRequestFor($notamChunk, ++$chunkNumber))
//            ->collapse();
//    }

    /**
     * Especially when using a free key, we can only send so many requests to the api per minute.
     *
     * This allows you to control the speed of the requests. If you accidentally send too many
     * per minute the system will pause until its back inside limits.
     *
     * Should not be an issue for paid key.
     *
     * @param $chunk
     * @param $chunkNumber
     * @return mixed
     */
//    protected function rateLimitTaggingRequestFor($chunk, $chunkNumber): mixed
//    {
//        if (RateLimiter::tooManyAttempts('open_ai_api_request', 60)) {
//            $seconds = RateLimiter::availableIn('open_ai_api_request');
//            $this->updateMessage(sprintf("Oops! - We're waiting for the api because we're hitting limits. Pausing for %s seconds", $seconds));
//            sleep($seconds + 3); //Add a small buffer
//        }
//
//        return RateLimiter::attempt(
//            'open_ai_api_request',
//            60, //number of attempts
//            fn () => $this->tagChunks($chunk, $chunkNumber),
//            60 //every xx seconds
//        );
//    }

    protected function tagChunks(): Collection
    {
        $startTime = now();
        $this->updateMessage(
            sprintf(
                "We are currently sending %s batches of notam requests simultaneously, totaling %s notams. Please be patient as this may take some time.\n\nStart Time is %s",
                $this->chunkedNotams->count(),
                $this->chunkedNotams->collapse()->count(),
                $startTime->format('d-m-Y H:i:s')
            ));

        $client = $this->getClient();

        $promises = $this->chunkedNotams
            ->map(fn ($batch) => $this->createTextContent($batch))
            ->map(fn ($text) => [
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0,
                'messages' => array_merge(
                    Prompt::get(),
                    [['role' => 'user', 'content' => $text]]
                ),
            ])
            ->tap(function (Collection $prompts) {
                if (config('openai.enable_log')) {
                    Log::info('Current Prompts:', $prompts->toArray());
                }
            })
            ->map(fn ($payload) => $client->postAsync('https://api.openai.com/v1/chat/completions', [
                'json' => $payload,
            ]))
            ->toArray();

        $responses = collect(Utils::unwrap($promises))
            ->map(fn ($response) => json_decode($response->getBody(), true))
            ->tap(function (Collection $responses) {
                if (config('openai.enable_log')) {
                    Log::info('Responses:', $responses->toArray());
                }
            })
            ->map(fn ($chatResult) => json_decode($chatResult['choices'][0]['message']['content'], true))
            ->collapse();

        $elapsedSecondsFormatted = number_format(now()->diffInSeconds($startTime), 1);

        $this->updateMessage(
            sprintf("Phewf Success! - We've just processed all %s Notams!\n\n It took %s seconds for OpenAi to process everything.",
                $this->chunkedNotams->collapse()->count(),
                $elapsedSecondsFormatted)
        );

        Log::info("OpenAI took $elapsedSecondsFormatted seconds to process {$this->chunkedNotams->count()} batches of notams totaling {$this->chunkedNotams->collapse()->count()} notams.");

        return $responses;

        //        $responses = Http::asJson()
        //            ->timeout(100)
        //            ->connectTimeout(100)
        //            ->withHeaders(['Host' => 'api.openai.com'])
        //            ->pool(function (Pool $pool) {
        //                $this->chunkedNotams
        //                    ->map(fn ($batch) => $this->createTextContent($batch))
        //                    ->map(fn ($notamsContent) => $this->sendOpenAiRequest($pool, $notamsContent));
        //            });

        return $this->formatResults($responses);
    }

    protected function updateMessage(string $message): void
    {
        if ($this->channelName) {
            event(new NotamProcessingEvent($this->channelName, $message));
        }
    }

    protected function createTextContent(Collection $batch): mixed
    {
        // Generate the text from all the notams in the batch. They should appear in the format
        // notamKey: notam text
        return $batch
            ->map(fn (array $notam) => "{$notam['key']}: ".($notam['message'] ?? $notam['all']))
            ->implode("\n\n\n\n");
    }

    protected function sendOpenAiRequest(Pool $pool, $notamsContent): PromiseInterface
    {
        return $pool
            ->connectTimeout(100)
            ->timeout(100)
            ->withToken(config('openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions',
                [
                    'model' => 'gpt-3.5-turbo',
                    'temperature' => 0,
                    'messages' => array_merge(
                        Prompt::get(),
                        [['role' => 'user', 'content' => $notamsContent]]
                    ),
                ]);
    }

    protected function addTaggedData(array $notam, Collection $taggedData): array
    {
        return collect($notam)
            ->union($taggedData->firstWhere('key', $notam['key']))
            ->toArray();
    }

    /**
     * @param $results array<Response>
     */
    protected function formatResults(array $results): Collection
    {
        return collect($results)
            ->map(function ($response) {
                return json_decode($response->json()['choices'][0]['message']['content'], true);
            })
            ->collapse();
    }

    protected function getClient(): Client
    {
        return new Client([
            'timeout' => 100,
            'connect_timeout' => 100,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.config('openai.api_key'),
            ],
        ]);
    }
}
