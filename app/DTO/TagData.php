<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class TagData extends Data
{
    public function __construct(
        public string $id,
        public string $type,
        public string $code,
        public string $summary,
    ) {
    }
}
