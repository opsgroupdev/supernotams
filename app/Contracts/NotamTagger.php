<?php

namespace App\Contracts;

use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Exceptions\TaggingConnectionException;
use App\Models\Notam;
use Exception;
use JsonException;

abstract class NotamTagger
{
    protected Notam $notam;

    protected LLM $llm;

    public function __construct()
    {
        $this->setLLM(LLM::GPT_3_5_TURBO);
    }

    /**
     * @throws JsonException
     * @throws TaggingConnectionException
     * @throws Exception
     */
    public function tag(Notam $notam)
    {
    }

    abstract public function setLLM(LLM $llm);

    protected function updateNotam(array $tagData): void
    {
        $this->notam->update(
            [
                'code'    => $tagData['code'],
                'type'    => $tagData['type'],
                'summary' => $tagData['summary'],
                'status'  => NotamStatus::TAGGED,
                'llm'     => $this->llm->value,
            ]);
    }

    protected function logData($aiResponse)
    {
    }
}
