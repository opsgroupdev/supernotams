<?php

use App\Utils\NotamRetriever;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('retreives notams from the server', function () {
    Http::fakeSequence()
        ->push(file_get_contents(base_path('tests/source/notamssource.json')));

    $aiportsAndFirs = collect(
        [
            'departureAirport'      => 'EIDW',
            'destinationAirport'    => 'EGLL',
            'destinationAlternates' => 'EGBB',
        ]);

    $notams = NotamRetriever::for($aiportsAndFirs);

    expect($notams)->toHaveCount(2)
        ->and($notams)->toHaveKeys(['EIDW', 'EGLL'])
        ->and($notams->collapse())->toHaveCount(59);

    Http::assertSent(function(Request $request){
        return $request->isJson() && $request->data() === ['type' => 'LOCATION', 'locs' => 'EIDW,EGLL'];
    });
});