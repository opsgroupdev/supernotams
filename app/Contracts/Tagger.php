<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface Tagger
{
    public function process(Collection $notams, ?string $channelName = null): Collection;
}
