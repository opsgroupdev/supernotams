<?php

namespace App\Enum;

enum LLM: int
{
    case GPT_3_5_TURBO = 0;
    case GPT_4 = 1;
    case GPT_4_TURBO = 2;

    public function label(): string
    {
        return match ($this) {
            self::GPT_3_5_TURBO => 'gpt-3.5-turbo',
            self::GPT_4         => 'gpt-4',
            self::GPT_4_TURBO   => 'gpt-4-turbo-preview',
        };
    }
};
