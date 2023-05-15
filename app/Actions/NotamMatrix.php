<?php

namespace App\Actions;

use Illuminate\Support\Collection;

class NotamMatrix
{
    protected function orderMatrix(): Collection
    {
        // @formatter:off
        return collect([
            'departureAirport' => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7'],
            'destinationAirport' => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7'],
            //            'destinationAlternates' => ['P1','P2','P3','P4','P5','P6','P7','P8','A1','A2','A3','A4','A5','R1','R2','R3','R4','R5','R6','T1','T2','T3','T4','T5','C1','C2','C3','C4','C5','C6','S4','S6','S7','H7'],
            //            'enrouteAlternates'     => ['P1','P2','P3','P4','A1','A2','R1','R2','R3','R4','R5','R6','C1','C2','C3'],
            //            'firs'                  => [],
        ]);
    }

    public static function process($airportsAndFirs, Collection $taggedNotams): Collection
    {
        return (new self())->filter($airportsAndFirs, $taggedNotams);
    }

    public function filter($airportsAndFirs, $taggedNotams): Collection
    {
        $taggedNotams = $taggedNotams->toArray();
        $primaryNotams = [];
        $appendixNotams = [];
        $order = $this->orderMatrix();

        foreach ($airportsAndFirs as $key => $airportCode) {
            if (isset($order[$key]) && isset($taggedNotams[$airportCode])) {
                $tagCodes = $order[$key];
                $notams = $taggedNotams[$airportCode];

                $primaryNotams[$key][$airportCode] = array_filter($notams, function ($notam) use ($tagCodes) {
                    return in_array($notam['TagCode'], $tagCodes);
                });

                $appendixNotams[$key][$airportCode] = array_values(array_diff_ukey($notams,
                    $primaryNotams[$key][$airportCode],
                    function ($key1, $key2) {
                        return $key1 <=> $key2;
                    }));

                // Rearrange the notams based on the order specified in $order
                usort($primaryNotams[$key][$airportCode], function ($a, $b) use ($tagCodes) {
                    $aIndex = array_search($a['TagCode'], $tagCodes);
                    $bIndex = array_search($b['TagCode'], $tagCodes);

                    return $aIndex - $bIndex;
                });
            }
        }

        return collect(['primary' => $primaryNotams, 'appendix' => $appendixNotams]);
    }
}
