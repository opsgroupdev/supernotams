<?php

use App\Actions\NotamFilter;
use App\Models\Notam;

it('sorts notams into primary and appendix categories in the correct order', function () {

    $taggedNotams = collect([
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'B1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'L2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'R1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'D3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'A6']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'A1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'A3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'A2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'R4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIDW', 'code' => 'R2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'A2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'H5']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'L2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'A4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'R1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'C4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'R3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'H1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'R2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EGAA', 'code' => 'P3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'A1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'A3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'R4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'R2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'R1']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'A5']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'L2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'H2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'H3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EINN', 'code' => 'R4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIKN', 'code' => 'P3']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIKN', 'code' => 'H2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIKN', 'code' => 'R4']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIKN', 'code' => 'A2']),
        Notam::factory()->tagged()->make(['id' => fake()->regexify("[ABC]\d{4}\/2[3-4]").'-EIKN', 'code' => 'S1']),
    ]);

    $airports = [
        'departureAirport'      => ['EGAA'],
        'destinationAirport'    => ['EIDW'],
        'destinationAlternates' => ['EINN', 'EIKN'],
    ];

    $notamFilter = new NotamFilter();
    $results = $notamFilter->filter($airports, $taggedNotams);

    expect($results['primary']['departureAirport']['EGAA']->pluck('code'))->toMatchArray(['P3', 'A2', 'A4', 'R1', 'R2', 'R3', 'C4']);
    expect($results['primary']['destinationAirport']['EIDW']->pluck('code'))->toMatchArray(['A1', 'A2', 'A3', 'R1', 'R2', 'R4']);
    expect($results['primary']['destinationAlternates']['EINN']->pluck('code'))->toMatchArray(['A1', 'A3', 'A5', 'R1', 'R2', 'R4']);
    expect($results['primary']['destinationAlternates']['EIKN']->pluck('code'))->toMatchArray(['P3', 'A2', 'R4']);

    expect($results['appendix']['departureAirport']['EGAA']->pluck('code'))->toMatchArray(['H5', 'L2', 'H1']);
    expect($results['appendix']['destinationAirport']['EIDW']->pluck('code'))->toMatchArray(['B1', 'L2', 'D3', 'A6']);
    expect($results['appendix']['destinationAlternates']['EINN']->pluck('code'))->toMatchArray(['L2', 'H2', 'H3']);
    expect($results['appendix']['destinationAlternates']['EIKN']->pluck('code'))->toMatchArray(['H2', 'S1']);
});
