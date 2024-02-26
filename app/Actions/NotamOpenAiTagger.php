<?php

namespace App\Actions;

use App\Contracts\NotamTagger;
use App\DTO\TagData;
use App\Enum\LLM;
use App\Exceptions\TaggingConnectionException;
use App\Models\Notam;
use App\OpenAI\Prompt;
use Exception;
use Illuminate\Support\Str;
use JsonException;
use Log;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;

class NotamOpenAiTagger extends NotamTagger
{
    protected Notam $notam;

    protected $aiResponse;

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function tag(Notam $notam): void
    {
        $this->notam = $notam;

        $this->updateNotam($this->openAiRequest());

        $this->logData();
    }

    protected function openAiRequest(): TagData
    {
        try {
            $this->aiResponse = OpenAI::chat()
                ->create([
                    'model'           => $this->llm->label(), //gpt-4, gpt-4-turbo-preview, gpt-3.5-turbo
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => array_merge(
                        Prompt::get(),
                        [['role' => 'user', 'content' => json_encode(['id' => $this->notam->id, 'text' => $this->notam->fullText])]]
                    ),
                ]);
        } catch (TransporterException $exception) {
            throw new TaggingConnectionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($this->aiResponse->choices[0]->finishReason !== 'stop') {
            throw new Exception("Open AI finish reason was {$this->aiResponse->choices[0]->finishReason}");
        }

        return new TagData(...$this->jsonDecodeAiResponse());
    }

    protected function logData(): void
    {
        Log::info(sprintf('Tag Success: %s - %s - Prompt: %s - Completion: %s - Total: %s - RqRemain: %s - RqReset: %s - TokRemain: %s - TokReset: %s',
            $this->notam->id,
            $this->aiResponse->model,
            $this->aiResponse->usage->promptTokens,
            $this->aiResponse->usage->completionTokens,
            $this->aiResponse->usage->totalTokens,
            $this->aiResponse->meta()->requestLimit->remaining,
            $this->aiResponse->meta()->requestLimit->reset,
            $this->aiResponse->meta()->tokenLimit->remaining,
            $this->aiResponse->meta()->tokenLimit->reset,
        ));
    }

    public function setLLM(LLM $llm): static
    {
        $this->llm = $llm;

        return $this;
    }

    protected function jsonDecodeAiResponse(): array
    {
        Log::debug('  Tag Data: '.$this->notam->id.' - '.Str::squish($this->aiResponse->choices[0]->message->content));

        return json_decode(
            json: $this->aiResponse->choices[0]->message->content,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }
}
