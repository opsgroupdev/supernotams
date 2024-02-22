<?php

namespace App\Jobs;

use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Models\Notam;
use App\OpenAI\Prompt;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

class NotamTagJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    protected int $startTime;

    public function __construct(protected readonly Notam $notam, protected readonly LLM $llm)
    {
        $this->onQueue('tagging');
    }

    public function handle(OpenAI $openAi): void
    {
        $this->startTime = hrtime(true);

        try {
            $aiResponse = $this->openAiRequest($openAi);

            if ($aiResponse->choices[0]->finishReason !== 'stop') {
                throw new Exception("Open AI finish reason was {$aiResponse->choices[0]->finishReason}");
            }

            $this->tagNotam($aiResponse);
        } catch (TransporterException $transporterException) {
            $this->retryWithDelay($transporterException);
        } catch (Exception $errorException) {
            $this->retryImmediately($errorException);
        }

        //  $this->simpleRateLimit();
    }

    /**
     * @throws TransporterException
     */
    protected function openAiRequest(OpenAI $openAi): CreateResponse
    {
        return $openAi::chat()
            ->create([
                'model'           => $this->llm->label(), //gpt-4, gpt-4-turbo-preview, gpt-3.5-turbo
                'response_format' => ['type' => 'json_object'],
                'messages'        => array_merge(
                    Prompt::get(),
                    [['role' => 'user', 'content' => json_encode($this->notam->structure)]]
                ),
            ]);
    }

    protected function tagNotam(CreateResponse $response): void
    {
        $result = json_decode(
            json: $response->choices[0]->message->content,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->notam->update(
            [
                'code'    => $result['code'],
                'type'    => $result['type'],
                'summary' => $result['summary'],
                'status'  => NotamStatus::TAGGED,
                'llm'     => $this->llm->value,
            ]);

        $this->logData($response);
    }

    protected function logData(CreateResponse $response): void
    {
        Log::info(sprintf('Tag Success: %s - %s - Prompt: %s - Completion: %s - Total: %s - RqRemain: %s - RqReset: %s - TokRemain: %s - TokReset: %s',
            $this->notam->id,
            $response->model,
            $response->usage->promptTokens,
            $response->usage->completionTokens,
            $response->usage->totalTokens,
            $response->meta()->requestLimit->remaining,
            $response->meta()->requestLimit->reset,
            $response->meta()->tokenLimit->remaining,
            $response->meta()->tokenLimit->reset,
        ));
    }

    protected function retryWithDelay(TransporterException $transporterException): void
    {
        //We should retry this with an exponential delay.
        Log::error("{$this->notam->id} - OpenAI Connection Issue: {$transporterException->getMessage()}");

        if ($this->attempts() === $this->tries) {
            $this->notam->update(['status' => NotamStatus::UNTAGGED]);
        } else {
            $this->release(
                now()->addMinutes(1 + ($this->attempts() - 1) * 15) //roughly 1, 15, 30, 45, 60min delays
            );
        }
    }

    protected function retryImmediately(Exception $errorException): void
    {
        //We should try this again immediately.
        Log::error("{$this->notam->id} - OpenAI Error: {$errorException->getMessage()}");

        $this->attempts() === $this->tries
            ? $this->notam->update(['status' => NotamStatus::ERROR])
            : $this->release();
    }

    /**
     * Ensure that this job takes a minimum of 2 seconds to run.
     *
     * With 3 workers processing the queue we can only hit the api 90 times a minutes. Well under the
     * rate limits.
     */
    protected function simpleRateLimit(): void
    {
        $totalTime = (hrtime(true) - $this->startTime) / 1e3; // convert to microseconds

        if ($totalTime < 2000000) {
            usleep(2000000 - $totalTime);
        }
    }
}
