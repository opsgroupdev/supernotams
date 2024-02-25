<?php

use App\Actions\NotamDAIPFetcher;
use App\DTO\StandardNotam;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('can fetch notams from the DAIP api', function () {
    Storage::fake('local');
    Http::fakeSequence()->push(file_get_contents(base_path('tests/source/daip_sample.json')));

    $airports = collect(['EICK,EIDW']);

    $notams = (new NotamDAIPFetcher())->get($airports);

    expect($notams)->toHaveCount(34);

    Http::assertSent(function (Request $request) {
        return $request->method() == 'POST'
            && $request->data() === ['type' => 'LOCATION', 'locs' => 'EICK,EIDW'];
    });

    expect($notams)->each()->toBeInstanceOf(StandardNotam::class);
    expect($notams->get(0)->id)->toBe('A0059/24-EIDW');
});

it('returns an empty collection if the notam server fails', function () {
    Http::fakeSequence()->push('error', 500);
    Log::shouldReceive('error')->once()->withSomeOfArgs([500, 'Internal Server Error', 'error']);

    $airports = collect(['EICK,EIDW']);

    $notams = (new NotamDAIPFetcher())->get($airports);

    expect($notams)->toHaveCount(0);
});
