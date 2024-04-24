<?php

namespace App\OpenAI;

use App\Enum\Tag as Enum;
use Illuminate\Support\Collection;

class Tags
{
    public static function all(): Collection
    {
        return collect(Enum::cases())
            ->map(fn (Enum $tag) => [
                $tag->name,
                $tag->type(),
                $tag->description(),
            ]);
    }

    public static function asJson(): string
    {
        return json_encode(self::all());
    }
}
