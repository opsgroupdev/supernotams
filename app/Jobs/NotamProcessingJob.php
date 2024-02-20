<?php

namespace App\Jobs;

use App\Actions\FlightPlanParser;
use App\Actions\NotamFetcherAction;
use App\Actions\NotamMatrix;
use App\Actions\PDFCreator;
use App\Events\NotamProcessingEvent;
use App\Events\NotamResultEvent;
use App\Events\PdfResultEvent;
use App\Models\Notam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
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
        NotamFetcherAction $notamRetriever,
        NotamMatrix $matrix,
        PDFCreator $PDFCreator,
    ): void {
        try {
            $this->sendMessage('Extracting all data out of the ATC flightplan');

            $airports = $fpParser->parse($this->flightPlan);
            $this->sendMessage($this->airportList($airports));

            $rawNotams = $notamRetriever->get($airports);
            $this->sendMessage("My goodness, what a lot of notams you've asked for! - I've just received {$rawNotams->count()} of them!<br /><br /> Time to process everything.");

            $taggedNotams = Notam::whereIn('id', $rawNotams->pluck('key'))->get();

            $filteredNotams = $matrix->filter($airports, $taggedNotams);
            $this->sendMessage('We got them all! Sending the results');
            event(new NotamResultEvent($this->channelName, $filteredNotams));

            $cacheKey = $PDFCreator->generate($filteredNotams);
            event(new PdfResultEvent($this->channelName, $cacheKey));

        } catch (Throwable $throwable) {
            report($throwable);
            $this->sendMessage($throwable->getMessage(), 'error');
        }
    }

    private function airportList(Collection $airportsAndFirs): string
    {
        return view('partials.airportList')->with('airports', $airportsAndFirs)->render();
    }

    protected function sendMessage(string $message, $type = 'success'): void
    {
        event(new NotamProcessingEvent($this->channelName, $message, $type));
    }
}
