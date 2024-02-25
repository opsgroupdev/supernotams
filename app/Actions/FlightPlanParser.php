<?php

namespace App\Actions;

use App\DTO\AtcFlightPlan;
use App\Enum\Airports;
use Exception;
use Spatie\Regex\Regex;

class FlightPlanParser
{
    public static function process(string $flightPlanText): AtcFlightPlan
    {
        return (new self())->parse($flightPlanText);
    }

    public function parse($flightPlanText): AtcFlightPlan
    {
        try {
            //Split the flightplan into sections. Each section is marked with a dash at start of the line
            $fields = str($flightPlanText)->split('/^ *?-/m')->toArray();

            //Extract slices of text that are required from some of those sections.
            $firs = Regex::match('/EET\/([\w\s]*)\s{1}[A-Z]+\//i', $fields[5])->groupOr(1, '');
            $enrAlt = Regex::match('/RALT\/([\w\s]*)\s{1}[A-Z]+\//i', $fields[5])->groupOr(1, '');
            $takeOffAlt = Regex::match('/TALT\/([\w\s]*)\s{1}[A-Z]+\//', $fields[5])->groupOr(1, '');

            //For some fields that have multiple airports, pull them into arrays.
            preg_match_all('/[A-Z]{4}/i', $fields[4], $destMatches, PREG_PATTERN_ORDER);
            preg_match_all('/\b([A-Z]{4})[0-9]{4}/i', $firs, $firMatches, PREG_PATTERN_ORDER);
            preg_match_all('/\b[A-Z]{4}/i', $enrAlt, $enrAltMatches, PREG_PATTERN_ORDER);

            //Populate our array with all the data.
            $locations = new AtcFlightPlan(
                departureAirport: collect([Regex::match('/[A-Z]{4}/i', $fields[2])->resultOr('')])->filter(),
                destinationAirport: collect([array_shift($destMatches[0])])->filter(),
                destinationAlternate: $destMatches[0] ? collect($destMatches[0]) : collect(),
                firs: isset($firMatches[1]) ? collect($firMatches[1])->unique()->values() : collect(),
                enrouteAlternates: $enrAltMatches[0] ? collect($enrAltMatches[0])->unique()->values() : collect(),
                takeoffAlternate: collect([Regex::match('/[A-Z]{4}/i', $takeOffAlt)->resultOr('')])->filter()
            );
        } catch (Exception $exception) {
            throw new Exception('Sorry unable to extract all the required details from the flight plan supplied. Please try again.', $exception->getCode(), $exception);
        }

        //TODO This is for the demo only. Check to make sure only airports in the UK, Ireland, Australia and New Zealand.
        $allowed = str(Airports::ALL)->upper()->explode(',');
        $requested = $locations->allLocations();

        if ($requested->diff($allowed)->count() > 0) {
            throw new Exception('Sorry, for this demo, you can strictly only submit ATC flight plan messages that contain major (i.e. the main/international) airports anywhere in Ireland, the United Kingdom, Australia, or New Zealand. '.$requested->diff($allowed)->implode(',').' is not allowed. Currently accepted airports are: '.str(Airports::ALL)->explode(',')->sort()->implode(','));
        }

        return $locations;
    }
}
