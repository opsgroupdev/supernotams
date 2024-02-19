<?php

namespace App\Jobs;

use App\Enum\NotamStatus;
use App\Models\Notam;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Log;
use Storage;

class NotamRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string|iterable $icaoIdents)
    {
    }

    public function handle(): void
    {
        $this->insertToDatabase($this->notamsFromApi());

        $this->dispatchNotamTagJobs();
    }

    protected function insertToDatabase(Collection $notams): void
    {
        Notam::upsert(
            $notams->map(fn ($notam) => [
                'id'        => $notam['key'],
                'structure' => json_encode($notam),
            ])
                ->toArray(),
            ['id'],
            [], //TODO, should we update the structure here?
        );
    }

    protected function notamsFromApi(): Collection
    {
        $response = Http::get('https://api.anbdata.com/anb/states/notams/notams-realtime-list', [
            'api_key'   => config('NotamsSource.icao_api_key'),
            'locations' => $this->locations(),
        ]);

        if ($response->failed()) {
            $this->reportError($response);

            return collect();
        }

        $this->log($response);

        return collect($response->json());
    }

    protected function locations(): string|iterable
    {
        return is_iterable($this->icaoIdents) ? collect($this->icaoIdents)->implode(',') : $this->icaoIdents;
    }

    protected function reportError(Response $response): void
    {
        //TODO - Where/who do we want to notify these errors to?
        Log::error(
            'Error retrieving Notams from server.',
            [$response->status(), $response->reason(), $response->body()]
        );
    }

    protected function dispatchNotamTagJobs(): void
    {
        Notam::query()
            ->where('status', NotamStatus::UNTAGGED)
            ->get()
            ->tap($this->markAsProcessing())
            ->each(fn (Notam $notam) => NotamTagJob::dispatch($notam));
    }

    protected function markAsProcessing(): Closure
    {
        return fn (Collection $notams) => Notam::whereIn('id', $notams->pluck('id'))
            ->update(['status' => NotamStatus::PROCESSING]);
    }

    protected function log(Response $response): void
    {
        Storage::disk('local')->put('responses/'.now()->format('Y-m-d_H_i_s').'.json', $response->body());
    }
}
