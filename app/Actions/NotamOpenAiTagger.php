<?php

namespace App\Actions;

use App\Contracts\NotamTagger;
use App\Enum\LLM;
use App\Exceptions\TaggingConnectionException;
use App\Models\Notam;
use App\OpenAI\Prompt;
use Exception;
use JsonException;
use Log;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;

class NotamOpenAiTagger extends NotamTagger
{
    protected Notam $notam;

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function tag(Notam $notam): void
    {
        $this->notam = $notam;

        $this->updateNotam($this->openAiRequest());
    }

    protected function openAiRequest()
    {
        try {
            $response = OpenAI::chat()
                ->create([
                    'model'           => $this->llm->label(), //gpt-4, gpt-4-turbo-preview, gpt-3.5-turbo
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => array_merge(
                        Prompt::get(),
                        [['role' => 'user', 'content' => $this->notam->fullText]]
                    ),
                ]);
        } catch (TransporterException $exception) {
            throw new TaggingConnectionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($response->choices[0]->finishReason !== 'stop') {
            throw new Exception("Open AI finish reason was {$response->choices[0]->finishReason}");
        }

        $this->logData($response);

        return json_decode(
            json: $response->choices[0]->message->content,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    protected function logData($aiResponse): void
    {
        Log::info(sprintf('Tag Success: %s - %s - Prompt: %s - Completion: %s - Total: %s - RqRemain: %s - RqReset: %s - TokRemain: %s - TokReset: %s',
            $this->notam->id,
            $aiResponse->model,
            $aiResponse->usage->promptTokens,
            $aiResponse->usage->completionTokens,
            $aiResponse->usage->totalTokens,
            $aiResponse->meta()->requestLimit->remaining,
            $aiResponse->meta()->requestLimit->reset,
            $aiResponse->meta()->tokenLimit->remaining,
            $aiResponse->meta()->tokenLimit->reset,
        ));
    }

    public function setLLM(LLM $llm): static
    {
        $this->llm = $llm;

        return $this;
    }
}
