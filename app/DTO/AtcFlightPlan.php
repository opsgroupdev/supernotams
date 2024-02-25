<?php

namespace App\DTO;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class AtcFlightPlan extends Data
{
    /**
     * @param  Collection<string>  $departureAirport
     * @param  Collection<string>  $destinationAirport
     * @param  ?Collection<string>  $destinationAlternate
     * @param  ?Collection<string>  $firs
     * @param  ?Collection<string>  $enrouteAlternates
     * @param  ?Collection<string>  $takeoffAlternate
     */
    public function __construct(
        public Collection $departureAirport,
        public Collection $destinationAirport,
        public ?Collection $destinationAlternate,
        public ?Collection $firs,
        public ?Collection $enrouteAlternates,
        public ?Collection $takeoffAlternate,
    ) {
    }

    public function allLocations(): Collection
    {
        return collect($this->toArray())->flatten()->filter()->unique();
    }
}
