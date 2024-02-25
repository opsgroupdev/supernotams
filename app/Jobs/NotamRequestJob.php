<?php

namespace App\Jobs;

use App\Contracts\NotamFetcher;
use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Models\Notam;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NotamRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string|iterable $icaoIdents, protected LLM $llm = LLM::GPT_3_5_TURBO)
    {
    }

    public function handle(): void
    {
        $this->insertToDatabase($this->notamsFromApi());

        $this->dispatchNotamTagJobs();
    }

    protected function insertToDatabase(Collection $notams): void
    {
        //TODO, should we update the structure here?
        Notam::upsert(
            $notams->toArray(),
            ['id'],
            [],
        );
    }

    protected function notamsFromApi(): Collection
    {
        return app(NotamFetcher::class)->get($this->locations());
    }

    protected function locations(): Collection
    {
        return is_iterable($this->icaoIdents) ? collect($this->icaoIdents) : str($this->icaoIdents)->explode(',');
    }

    protected function dispatchNotamTagJobs(): void
    {
        Notam::query()
            ->where('status', NotamStatus::UNTAGGED)
            ->get()
            ->tap($this->markAsProcessing())
            ->each(fn (Notam $notam) => NotamTagJob::dispatch($notam, $this->llm));
    }

    protected function markAsProcessing(): Closure
    {
        return fn (Collection $notams) => Notam::whereIn('id', $notams->pluck('id'))
            ->update(['status' => NotamStatus::PROCESSING]);
    }
}
