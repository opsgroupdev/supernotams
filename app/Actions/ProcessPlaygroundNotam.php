<?php

namespace App\Actions;

use App\DTO\TagData;
use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Enum\Tag;
use App\Exceptions\TaggingConnectionException;
use App\Models\PlaygroundNotam;
use App\OpenAI\Prompt;
use Exception;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;

class ProcessPlaygroundNotam
{
    protected PlaygroundNotam $notam;

    protected LLM $llm = LLM::GPT_3_5_TURBO;

    public function process(PlaygroundNotam $notam): void
    {
        $this->notam = $notam;

        $this->setNotamAsProcessing();

        try {
            $aiData = $this->getAiData();
        } catch (Exception) {
            $this->setNotamAsErrored();

            return;
        }

        $this->storeAiData($aiData);
    }

    public function setLlm(LLM $llm): static
    {
        $this->llm = $llm;

        return $this;
    }

    protected function getAiData(): TagData
    {
        try {
            $response = OpenAI::chat()
                ->create([
                    'model'           => $this->llm->label(),
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => array_merge(
                        Prompt::get(),
                        [[
                            'role'    => 'user',
                            'content' => json_encode([
                                'id'   => $this->notam->id,
                                'text' => $this->notam->text,
                            ]),
                        ]]
                    ),
                ])
                ->choices[0];
        } catch (TransporterException $exception) {
            throw new TaggingConnectionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($response->finishReason !== 'stop') {
            throw new Exception("Open AI finish reason was $response->finishReason");
        }

        return new TagData(...json_decode(
            json: $response->message->content,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        ));
    }

    protected function setNotamAsProcessing(): void
    {
        $this->notam->update([
            'llm'    => $this->llm,
            'status' => NotamStatus::PROCESSING,
        ]);
    }

    protected function setNotamAsErrored(): void
    {
        $this->notam->update([
            'llm'          => $this->llm,
            'status'       => NotamStatus::ERROR,
            'processed_at' => now(),
        ]);
    }

    protected function storeAiData(TagData $tagData): void
    {
        $this->notam->update([
            'tag'          => constant(Tag::class.'::'.$tagData->code),
            'summary'      => $tagData->summary,
            'llm'          => $this->llm,
            'status'       => NotamStatus::TAGGED,
            'processed_at' => now(),
        ]);
    }
}
