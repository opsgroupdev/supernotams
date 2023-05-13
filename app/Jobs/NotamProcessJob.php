<?php

namespace App\Jobs;

use App\Events\NotamProcessingEvent;
use App\Events\NotamResultEvent;
use App\Utils\FlightPlanParser;
use App\Utils\NotamRetriever;
use App\Utils\OpenAiTagger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;

class NotamProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected $flightPlan, protected $channelName)
    {
    }

    public function handle(): void
    {
        event(new NotamProcessingEvent($this->channelName, 'Extracting all data out of the ATC flightplan'));

        $airportsAndFirs = FlightPlanParser::process($this->flightPlan);

        event(new NotamProcessingEvent($this->channelName, $this->formatMessageAboutAirportList($airportsAndFirs)));

        $notams = NotamRetriever::for($airportsAndFirs);

        event(new NotamProcessingEvent($this->channelName, "My goodness! What a lot of Notams you've asked for! - I've just received {$notams->collapse()->count()} of them! Time to process. This might take a while."));

        $taggedNotams = OpenAiTagger::tag($notams, $this->channelName);

        event(new NotamProcessingEvent($this->channelName, 'We got them all! Sending the results'));

        event(new NotamResultEvent($this->channelName, $taggedNotams));
    }

    private function formatMessageAboutAirportList(\Illuminate\Support\Collection $airportsAndFirs)
    {
        return sprintf("Cool cool cool - So this is what we're working with:<br /><br />Departure: %s<br />Destination: %s<br />Dest Alts: %s<br />FIRS: %s<br />En-route Alts: %s<br />TO Alts: %s",
            $airportsAndFirs['departureAirport'],
            $airportsAndFirs['destinationAirport'],
            $airportsAndFirs['destinationAlternates']->implode(','),
            $airportsAndFirs['firs']->implode(','),
            $airportsAndFirs['enrouteAlternates']->implode(','),
            $airportsAndFirs['takeoffAlternate']);
    }

}
