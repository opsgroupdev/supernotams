<?php

namespace App\Actions;

use App\Contracts\NotamFetcher;
use App\DTO\StandardNotam;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class NotamICAOFetcher extends NotamFetcher
{
    /**
     * @return Collection<int, StandardNotam>
     */
    public function get(Collection $icaoLocations): Collection
    {
        $response = Http::withUserAgent(config('app.user-agent'))
            ->connectTimeout(60)
            ->timeout(60)
            ->get('https://api.anbdata.com/anb/states/notams/notams-realtime-list', [
                'api_key'   => config('NotamsSource.icao_api_key'),
                'locations' => $icaoLocations->flatten()->unique()->implode(','),
            ]);

        if ($response->failed()) {
            $this->reportError($response);

            return collect();
        }

        $this->log($response);

        return $this->normaliseNotams($response);
    }

    /**
     * @return Collection<int, StandardNotam>
     */
    protected function normaliseNotams(Response $response): Collection
    {
        return collect($response->object())
            ->map(fn ($sourceNotam) => new StandardNotam(
                id: $sourceNotam->key,
                fullText: $sourceNotam->all,
                source: json_encode($sourceNotam),
            ))
            ->sort()
            ->values();
    }
}
