<?php

namespace App\Utils;

use Illuminate\Support\Collection;
use Spatie\Regex\MatchResult;
use Spatie\Regex\Regex;

class FlightPlanParser
{
    protected readonly string $flightPlanFields;
    protected readonly string $icaoCode;

    public function __construct(protected string $flightPlan)
    {
        $this->flightPlanFields = '/-[A-Z0-9\/\s]*/i';
        $this->icaoCode = '/[A-Z]{4}/i';
    }

    public static function process(string $flightPlanText)
    {
        return (new self($flightPlanText))->allAirports();
    }

    /**
     * @return Collection{
     *     departureAirport: string,
     *     destinationAirport: string,
     *     destinationAlternates: array<string>,
     *     firs: array<string>,
     *     enrouteAlternates: array<string>,
     *     takeoffAlternates: string,
     * }
     */
    protected function allAirports(): Collection
    {
        $flightplan = collect();

        $fields = Regex::matchAll($this->flightPlanFields, $this->flightPlan)->results();
        $destinations = Regex::matchAll($this->icaoCode, $fields[6]->result())->results();
        $eet = Regex::match('/EET\/([A-Za-z0-9\s]*)\s{1}[A-Za-z]+\//i', $fields[7]->result())->result();
        $ralt = Regex::match('/RALT\/([A-Za-z0-9\s]*)\s{1}[A-Za-z]+\//', $fields[7]->result());
        $enrouteAlt = Regex::matchAll($this->icaoCode, $ralt->resultOr(''))->results();
        $talt = Regex::match('/TALT\/([A-Za-z0-9\s]*)\s{1}[A-Za-z]+\//', $fields[7]->result());

        $flightplan['departureAirport'] = Regex::match($this->icaoCode, $fields[4]->result())->result();
        $flightplan['destinationAirport'] = array_shift($destinations)->result();
        $flightplan['destinationAlternates'] = collect(array_map(fn ($dest) => $dest->result(), $destinations));
        $flightplan['firs'] = collect(Regex::matchAll('/\b([A-Za-z]{4})[0-9]{4}/', $eet)->results())->map(fn (
            MatchResult $match
        ) => $match->group(1))->unique()->values();
        $flightplan['enrouteAlternates'] = collect(array_map(fn ($enr) => $enr->result(), $enrouteAlt));
        $flightplan['takeoffAlternate'] = Regex::match($this->icaoCode, $talt->groupOr(1, ''))->result();

        return $flightplan;
    }

}