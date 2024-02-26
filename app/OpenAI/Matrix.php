<?php

namespace App\OpenAI;

use Illuminate\Support\Collection;

class Matrix
{
    public static function get($key = null): Collection
    {
        // @formatter:off
        $matrixData = collect([
            'departureAirport'      => collect(['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7']),
            'destinationAirport'    => collect(['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7']),
            'destinationAlternates' => collect(['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'A1', 'A2', 'A3', 'A4', 'A5', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'T1', 'T2', 'T3', 'T4', 'T5', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'S4', 'S6', 'S7', 'H7']),
            'enrouteAlternates'     => collect(['P1', 'P2', 'P3', 'P4', 'A1', 'A2', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'C1', 'C2', 'C3']),
            'firs'                  => collect(), //As per the document no FIR primary notams. All appendix.
        ]);

        // Return the specified collection or the entire matrix
        return $key ? ($matrixData[$key] ?? collect()) : $matrixData;
    }

    public static function has($key): bool
    {
        return self::get()->has($key);
    }
}
