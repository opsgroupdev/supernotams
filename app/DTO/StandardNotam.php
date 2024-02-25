<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class StandardNotam extends Data
{
    public function __construct(
        public string $id,
        public string $fullText,
        public string $source,
    ) {
    }
}
