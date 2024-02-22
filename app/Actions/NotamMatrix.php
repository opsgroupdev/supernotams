<?php

namespace App\Actions;

use App\Models\Notam;
use Illuminate\Support\Collection;

class NotamMatrix
{
    protected function orderMatrix(): Collection
    {
        // @formatter:off
        return collect([
            'departureAirport'      => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7'],
            'destinationAirport'    => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7'],
            'destinationAlternates' => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7'],
            'enrouteAlternates'     => ['P1', 'P2', 'P3', 'P4', 'A1', 'A2', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'C1', 'C2', 'C3'],
            'firs'                  => [], //As per the document no FIR primary notams. All appendix.
        ]);
    }

    public static function process($airports, Collection $taggedNotams): Collection
    {
        return (new self())->filter($airports, $taggedNotams);
    }

    public function filter($airports, $taggedNotams): Collection
    {
        $primaryNotams = [];
        $appendixNotams = [];
        $matrixOrder = $this->orderMatrix();
        $taggedNotams = $taggedNotams->groupBy(fn (Notam $notam) => $notam->structure['location']);

        foreach ($airports as $airportType => $airportCodes) {
            foreach ($airportCodes as $airportCode) {
                if ($this->something($matrixOrder, $airportType, $taggedNotams, $airportCode)) {
                    $codes = collect($matrixOrder[$airportType]);
                    $notamsForAirport = $taggedNotams->get($airportCode);

                    $filteredNotams = $notamsForAirport->filter(fn ($notam) => $codes->contains($notam['code']));

                    // Rearrange the notams based on the order specified in the matrix
                    $primaryNotams[$airportType][$airportCode] = $filteredNotams->sortBy(fn ($notam) => $codes->search($notam['code']))->values();
                    $appendixNotams[$airportType][$airportCode] = $notamsForAirport->diffKeys($filteredNotams)->values();
                }
            }
        }

        return collect(['primary' => $primaryNotams, 'appendix' => $appendixNotams]);
    }

    protected function something(Collection $matrixOrder, int|string $airportType, mixed $taggedNotams, mixed $airportCode): bool
    {
        return isset($matrixOrder[$airportType]) && $taggedNotams->has($airportCode);
    }
}
