<?php

namespace App\Actions;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Log;
use Storage;

class NotamFetcherAction
{
    public static function fetch(Collection $airports): Collection
    {
        return (new self())->get($airports);
    }

    public function get(Collection $airports): Collection
    {
        $response = Http::get('https://api.anbdata.com/anb/states/notams/notams-realtime-list', [
            'api_key'   => config('NotamsSource.icao_api_key'),
            'locations' => $airports->flatten()->unique()->implode(','),
        ]);

        if ($response->failed()) {
            $this->reportError($response);

            return collect();
        }

        $this->log($response);

        return collect($response->json());
    }

    protected function reportError(Response $response): void
    {
        //TODO - Where/who do we want to notify these errors to?
        Log::error(
            'Error retrieving Notams from server.',
            [$response->status(), $response->reason(), $response->body()]
        );
    }

    protected function log(Response $response): void
    {
        Storage::disk('local')->put('responses/'.now()->format('Y-m-d_H_i_s').'.json', $response->body());
    }
}
