<?php

namespace App\Jobs;

use App\Actions\FlightPlanParser;
use App\Actions\NotamMatrix;
use App\Actions\NotamRetriever;
use App\Actions\PDFCreator;
use App\Contracts\Tagger;
use App\Events\NotamProcessingEvent;
use App\Events\NotamResultEvent;
use App\Events\PdfResultEvent;
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
        NotamRetriever $notamRetriever,
        Tagger $tagger,
        NotamMatrix $matrix,
        PDFCreator $PDFCreator,
    ): void {
        try {
            $this->sendMessage('Extracting all data out of the ATC flightplan');

            $airportsAndFirs = $fpParser->parse($this->flightPlan);
            $this->sendMessage($this->airportList($airportsAndFirs));

            $notams = $notamRetriever->icaoSource($airportsAndFirs);
            $this->sendMessage("My goodness, what a lot of notams you've asked for! - I've just received {$notams->count()} of them!<br /><br /> Time to process everything, this will take a little while.");

            $taggedNotams = $tagger->process($notams, $this->channelName);

            $filteredNotams = $matrix->filter($airportsAndFirs, $taggedNotams);
            $this->sendMessage('We got them all! Sending the results');
            event(new NotamResultEvent($this->channelName, $filteredNotams));

            $cacheKey = $PDFCreator->generate($filteredNotams);
            event(new PdfResultEvent($this->channelName, $cacheKey));

        } catch (Throwable $throwable) {
            $this->sendMessage($throwable->getMessage(), 'error');
        }
    }

    private function airportList(\Illuminate\Support\Collection $airportsAndFirs): string
    {
        return sprintf("Cool cool cool - so this is what we're working with:<br />
<table class='table table-auto mx-auto w-1/2 border'>
<tr class='border'>
<td class='p-2 text-gray-800'>Departure</td>
<td class='p-2 text-gray-600'>%s</td>
</tr>
<tr class='border'>
<td class='p-2 text-gray-800'>Destination</td>
<td class='p-2 text-gray-600'>%s</td>
</tr>
<tr class='border'>
<td class='p-2 text-gray-800'>Dest Alts</td>
<td class='p-2 text-gray-600'>%s</td>
</tr>
<tr class='border'>
<td class='p-2 text-gray-800'>Firs</td>
<td class='p-2 text-gray-600'>%s</td>
</tr>
<tr class='border'>
<td class='p-2 text-gray-800'>En-route Alts</td>
<td class='p-2 text-gray-600'>%s</td>
</tr>
<tr>
<td class='p-2 text-gray-800'>TO Alts</tdv>
<td class='p-2 text-gray-600'>%s</td>
</tr>
</table>
",
            $airportsAndFirs['departureAirport'],
            $airportsAndFirs['destinationAirport'],
            implode(',', $airportsAndFirs['destinationAlternates']),
            implode(',', $airportsAndFirs['firs']),
            implode(',', $airportsAndFirs['enrouteAlternates']),
            $airportsAndFirs['takeoffAlternate']);
    }

    protected function sendMessage(string $message, $type = 'success'): void
    {
        event(new NotamProcessingEvent($this->channelName, $message, $type));
    }
}
