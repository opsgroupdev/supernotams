<?php

use App\Actions\NotamFetcherAction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('hh', function () {
    $notams = NotamFetcherAction::fetch(collect(['EIDW', 'EINN', 'EGAA', 'EICK', 'EIKN']));

});

it('gets notams from the icao source when a collection is given', function () {
    Storage::fake('local');
    Http::fakeSequence()->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));

    $airports = collect(
        [
            'departureAirport'      => 'EIDW',
            'destinationAirport'    => 'EGLL',
            'destinationAlternates' => 'EHAM',
        ]);

    $notams = NotamFetcherAction::fetch($airports);

    expect($notams)->toHaveCount(60);

    Http::assertSent(function (Request $request) {
        return $request->method() == 'GET'
            && $request->data() === ['api_key' => 'notamsprint', 'locations' => 'EIDW,EGLL,EHAM'];
    });
});
