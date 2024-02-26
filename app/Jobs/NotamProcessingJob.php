<?php

namespace App\Jobs;

use App\Actions\FlightPlanParser;
use App\Actions\NotamFilter;
use App\Actions\PDFCreator;
use App\Contracts\NotamFetcher;
use App\DTO\AtcFlightPlan;
use App\Events\NotamProcessingEvent;
use App\Events\NotamResultEvent;
use App\Events\PdfResultEvent;
use App\Models\Notam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class NotamProcessingJob implements ShouldQueue
{
    public int $timeout = 600;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $flightPlan, protected string $channelName)
    {
    }

    public function handle(
        FlightPlanParser $fpParser,
        NotamFetcher $notamFetcher,
        NotamFilter $notamFilter,
        PDFCreator $PDFCreator,
    ): void {
        try {
            $this->sendMessage('Extracting all data out of the ATC flightplan');

            $icaoLocations = $fpParser->parse($this->flightPlan);
            $this->sendMessage($this->tableOf($icaoLocations));

            $this->sendMessage("Fetching all valid notams for your {$icaoLocations->allLocations()->count()} locations.");
            $rawNotams = $notamFetcher->get($icaoLocations->allLocations());
            $this->sendMessage("Just received {$rawNotams->count()} valid notams!<br /><br /> Time to process everything.");

            $taggedNotams = Notam::whereIn('id', $rawNotams->pluck('id'))->get();

            $filteredNotams = $notamFilter->filter($icaoLocations, $taggedNotams);
            $this->sendMessage('We got them all! Sending the results');
            event(new NotamResultEvent($this->channelName, $filteredNotams));

            $cacheKey = $PDFCreator->generate($filteredNotams);
            event(new PdfResultEvent($this->channelName, $cacheKey));

        } catch (Throwable $throwable) {
            report($throwable);
            $this->sendMessage($throwable->getMessage(), 'error');
        }
    }

    private function tableOf(AtcFlightPlan $icaoLocations): string
    {
        return view('partials.tableOfLocations')->with('icaoLocations', $icaoLocations)->render();
    }

    protected function sendMessage(string $message, $type = 'success'): void
    {
        event(new NotamProcessingEvent($this->channelName, $message, $type));
    }
}
