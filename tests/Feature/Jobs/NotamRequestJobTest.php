<?php

use App\Enum\NotamStatus;
use App\Jobs\NotamRequestJob;
use App\Jobs\NotamTagJob;
use App\Models\Notam;
use Illuminate\Support\Carbon;

it('stores received notams to the database and triggers tagging jobs', function () {
    Storage::fake('local');
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));
    $this->expectsDatabaseQueryCount(4);

    $job = new NotamRequestJob('eidw,einn');
    $job->handle();

    $allNotams = Notam::all();
    expect($allNotams)->toHaveCount(60);
    expect($allNotams->pluck('status'))->each()->toBe(NotamStatus::PROCESSING);
    Bus::assertDispatchedTimes(NotamTagJob::class, 60);
});

it('saves a good notam api response to a file', function () {
    Carbon::setTestNow('2024-02-18 13:15:30');
    Storage::fake('local');
    Storage::assertDirectoryEmpty('responses');
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));

    $job = new NotamRequestJob('eidw,einn');
    $job->handle();

    Storage::assertExists('responses/2024-02-18_13_15_30.json');
});

it('does not save a bad notam api response to a file', function () {
    Carbon::setTestNow('2024-02-18 13:15:30');
    Storage::fake('local');
    Storage::assertDirectoryEmpty('responses');
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push('Server error', 500);

    $job = new NotamRequestJob('eidw,einn');
    $job->handle();

    Storage::assertDirectoryEmpty('responses');
});

it('does not DUPLICATE notams already in the database', function () {
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()
        ->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')))
        ->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));

    expect(Notam::count())->toBe(0);
    $job = new NotamRequestJob('eidw,einn');
    $job->handle();
    expect(Notam::count())->toBe(60);

    $job = new NotamRequestJob('eidw,einn');
    $job->handle();
    expect(Notam::count())->toBe(60);
    Bus::assertDispatchedTimes(NotamTagJob::class, 60);
});

it('does not UPDATE the structure of a notam if it already exists', function () {
    Bus::fake();
    Storage::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')));

    //A notam has been saved to database with Qcode of ZZZZ in the structure.
    $sourceStructure = [
        'id'          => 'A1226/23',
        'entity'      => 'MX',
        'status'      => 'LC',
        'Qcode'       => 'ZZZZ',
        'Area'        => 'AGA',
        'SubArea'     => 'Movement and landing area',
        'Condition'   => 'Limitations',
        'Subject'     => 'Taxiway(s)',
        'Modifier'    => 'Closed',
        'message'     => "TWY E1 CLOSED\nREF AIP SUPPLEMENT 021/2022.\nCREATED: 12 May 2023 20:08:00 \nSOURCE: EUECYIYN",
        'startdate'   => '2023-05-15T18:30:00.000Z',
        'enddate'     => '2023-05-20T03:15:00.000Z',
        'all'         => "A1226/23 NOTAMN\nQ) EISN/QMXLC/IV/BO /A /000/999/5325N00616W005\nA) EIDW B) 2305151830 C) 2305200315\nD) DAILY 1830-0315\nE) TWY E1 CLOSED\nREF AIP SUPPLEMENT 021/2022.\nCREATED: 12 May 2023 20:08:00 \nSOURCE: EUECYIYN",
        'location'    => 'EIDW',
        'isICAO'      => true,
        'Created'     => '2023-05-12T20:08:00.000Z',
        'key'         => 'A1226/23-EIDW',
        'type'        => 'airport',
        'StateCode'   => 'IRL',
        'StateName'   => 'Ireland',
        'criticality' => -1,
    ];

    $taggedNotam = Notam::factory()->create([
        'id'        => 'A1226/23-EIDW',
        'structure' => $sourceStructure,
        'code'      => 'A1',
        'summary'   => 'Some Short Description',
        'type'      => 'Airport Issue',
    ]);

    expect($taggedNotam->structure['Qcode'])->toBe('ZZZZ');
    expect(Notam::count())->toBe(1);

    sleep(1); //Otherwise the update_at time would not change as test is too fast.

    //Now we get a new batch of notams. Which includes a notam already in the database.
    //At the moment we do not update the notam structure
    $job = new NotamRequestJob('eidw,einn');
    $job->handle();
    expect(Notam::count())->toBe(60);

    $updatedNotam = Notam::find('A1226/23-EIDW');
    expect($updatedNotam->structure['Qcode'])->toContain('ZZZZ');
    expect($updatedNotam->code)->toBe('A1'); //Ensure notam tags have not been removed etc
    expect($updatedNotam->type)->toBe('Airport Issue');
    expect($updatedNotam->updated_at)->not()->toEqual($taggedNotam->updated_at); //Prove it was updated/touched.
});

it('logs an error if unable to connect to server or retrieve notams', function () {
    Bus::fake();
    Storage::fake('local');
    Http::preventStrayRequests();
    Http::fakeSequence()->push('Error with Server', 500);
    Log::shouldReceive('error')->once()->withSomeOfArgs([500, 'Internal Server Error', 'Error with Server']);

    $job = new NotamRequestJob('eidw,einn');
    $job->handle();

    expect(Notam::count())->toBe(0);
    Bus::assertNothingDispatched();
});
