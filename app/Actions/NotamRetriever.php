<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class NotamRetriever
{
    public static function fromIcaoSource(Collection $airportsAndFirs): Collection
    {
        return (new self())->icaoSource($airportsAndFirs);
    }

    public static function fromDaipSource(Collection $airportsAndFirs): Collection
    {
        return (new self())->daipSource($airportsAndFirs);
    }

    public function icaoSource(Collection $airportsAndFirs): Collection
    {
        $response = Http::get('https://api.anbdata.com/anb/states/notams/notams-realtime-list', [
            'api_key' => config('NotamsSource.icao_api_key'),
            'locations' => $airportsAndFirs->only(['departureAirport', 'destinationAirport'])->implode(','),
        ]);

        return collect($response->json());
    }

    public function daipSource(Collection $airportsAndFirs): Collection
    {
        //Not currently in use.

        $response = Http::asJson()
            ->withoutVerifying()
            ->post('https://www.daip.jcs.mil/daip/mobile/query', [
                'type' => 'LOCATION',
                'locs' => $airportsAndFirs->only(['departureAirport', 'destinationAirport'])->implode(','),
            ]);

        return collect($response->json('group'))
            ->pluck('notams.0') //Data is buried in another array
            ->mapWithKeys(fn (array $notamData) => [$notamData['code'] => $notamData['list']]);
    }
}
