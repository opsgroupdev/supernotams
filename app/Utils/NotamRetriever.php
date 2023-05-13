<?php

namespace App\Utils;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class NotamRetriever
{
    public function __construct(protected Collection $airportsAndFirs)
    {
    }

    public static function for(Collection $airportsAndFirs): Collection
    {
        return (new self($airportsAndFirs))->get();
    }

    protected function get(): Collection
    {
        $response = Http::asJson()
            ->withoutVerifying()
            ->post('https://www.daip.jcs.mil/daip/mobile/query', [
                "type" => "LOCATION",
                "locs" => $this->airportsAndFirs->only(['departureAirport', 'destinationAirport'])->implode(','),
            ]);

        return collect($response->json('group'))
            ->pluck('notams.0') //Data is buried in another array
            ->mapWithKeys(fn (array $notamData) => [$notamData['code'] => $notamData['list']]);
    }
}