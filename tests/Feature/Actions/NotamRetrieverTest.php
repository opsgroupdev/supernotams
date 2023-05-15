<?php

use App\Actions\NotamRetriever;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('gets notams from the icao source', function () {
    Http::fakeSequence()
        ->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));

    $aiportsAndFirs = collect(
        [
            'departureAirport' => 'EIDW',
            'destinationAirport' => 'EGLL',
            'destinationAlternates' => 'EHAM',
        ]);

    $notams = NotamRetriever::fromIcaoSource($aiportsAndFirs);

    expect($notams)->toHaveCount(60);

    Http::assertSent(function (Request $request) {
        return $request->method() == 'GET'
            && $request->data() === ['api_key' => 'notamsprint', 'locations' => 'EIDW,EGLL'];
    });
});

it('retreives notams from the daip server', function () {
    Http::fakeSequence()
        ->push(file_get_contents(base_path('tests/source/MIL_Notam_Source.json')));

    $airportsAndFirs = collect(
        [
            'departureAirport' => 'EIDW',
            'destinationAirport' => 'EGLL',
            'destinationAlternates' => 'EGBB',
        ]);

    $notams = NotamRetriever::fromDaipSource($airportsAndFirs);

    expect($notams)->toHaveCount(2)
        ->and($notams)->toHaveKeys(['EIDW', 'EGLL'])
        ->and($notams->collapse())->toHaveCount(59);

    Http::assertSent(function (Request $request) {
        return $request->isJson() && $request->data() === ['type' => 'LOCATION', 'locs' => 'EIDW,EGLL'];
    });
});
