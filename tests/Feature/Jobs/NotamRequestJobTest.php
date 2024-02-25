<?php

use App\Enum\NotamStatus;
use App\Jobs\NotamRequestJob;
use App\Jobs\NotamTagJob;
use App\Models\Notam;
use Illuminate\Support\Carbon;

it('logs an error if unable to connect to server or retrieve notams', function () {
    Bus::fake();
    Storage::fake('local');
    Http::preventStrayRequests();
    Http::fakeSequence()->push('Error with Server', 500);
    Log::shouldReceive('error')->once()->withSomeOfArgs([500, 'Internal Server Error', 'Error with Server']);

    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();

    expect(Notam::count())->toBe(0);
    Bus::assertNothingDispatched();
});

it('stores received notams to the database and triggers tagging jobs', function () {
    Storage::fake('local');
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push(File::get(base_path('tests/source/icao_sample.json')));
    $this->expectsDatabaseQueryCount(4);

    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();

    $allNotams = Notam::all();
    expect($allNotams)->toHaveCount(34);
    expect($allNotams->pluck('status'))->each()->toBe(NotamStatus::PROCESSING);
    Bus::assertDispatchedTimes(NotamTagJob::class, 34);
});

it('saves a good notam api response to a file', function () {
    Carbon::setTestNow('2024-02-18 13:15:30');
    Storage::fake('local');
    Storage::assertDirectoryEmpty('responses');
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->push(File::get(base_path('tests/source/icao_sample.json')));

    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();

    Storage::assertExists('responses/2024-02-18_13_15_30.json');
});

it('does not save a bad notam api response to a file', function () {
    Carbon::setTestNow('2024-02-18 13:15:30');
    Storage::fake('local');
    Storage::assertDirectoryEmpty('responses');
    Bus::fake();
    Log::shouldReceive('error')->once()->withSomeOfArgs([500, 'Internal Server Error', 'Server error']);
    Http::preventStrayRequests();
    Http::fakeSequence()->push('Server error', 500);

    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();

    Storage::assertDirectoryEmpty('responses');
});

it('does not DUPLICATE notams already in the database', function () {
    Bus::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()
        ->pushFile(base_path('tests/source/icao_sample.json'))
        ->pushFile(base_path('tests/source/icao_sample.json'));

    expect(Notam::count())->toBe(0);
    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();
    expect(Notam::count())->toBe(34);

    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();
    expect(Notam::count())->toBe(34);
    Bus::assertDispatchedTimes(NotamTagJob::class, 34);
});

it('does not UPDATE the structure of a notam if it already exists', function () {
    Bus::fake();
    Storage::fake();
    Http::preventStrayRequests();
    Http::fakeSequence()->pushFile(base_path('tests/source/icao_sample.json'));

    //A notam has been saved to database with Qcode of ZZZZ in the structure.
    $sourceStructure = [
        'id'          => 'B1124/23',
        'entity'      => 'IG',
        'status'      => 'XX',
        'Qcode'       => 'ZZZZ',
        'Area'        => 'CNS',
        'SubArea'     => 'Instrument and microwave landing systems',
        'Condition'   => 'Limitations',
        'Subject'     => 'Taxiway(s)',
        'Modifier'    => 'Closed',
        'message'     => 'ILS GP RWY 16 HAS A RDH OF 50FT REF AIP EIDW AD 2.19 REMARKS TABLE CREATED: 24 Aug 2023 14:27:00 SOURCE: EUECYIYN',
        'startdate'   => '2023-05-15T18:30:00.000Z',
        'enddate'     => '2023-05-20T03:15:00.000Z',
        'all'         => "B1124/23 NOTAMN\nQ) EISN/QIGXX/I /NBO/A /000/999/5325N00616W005\nA) EIDW B) 2308241424 C) PERM\nE) ILS GP RWY 16 HAS A RDH OF 50FT\nREF AIP EIDW AD 2.19 REMARKS TABLE\nCREATED: 24 Aug 2023 14:27:00 \nSOURCE: EUECYIYN",
        'location'    => 'EIDW',
        'isICAO'      => true,
        'Created'     => '2023-05-12T20:08:00.000Z',
        'key'         => 'B1124/23-EIDW',
        'type'        => 'airport',
        'StateCode'   => 'IRL',
        'StateName'   => 'Ireland',
        'criticality' => -1,
    ];

    $taggedNotam = Notam::factory()->create([
        'id'      => 'B1124/23-EIDW',
        'code'    => 'A1',
        'summary' => 'Some Short Description',
        'type'    => 'Airport Issue',
        'source'  => $sourceStructure,
    ]);

    expect($taggedNotam->source['Qcode'])->toBe('ZZZZ');
    expect(Notam::count())->toBe(1);

    sleep(1); //Otherwise the update_at time would not change as test is too fast.

    //Now we get a new batch of notams. Which includes a notam already in the database.
    //At the moment we do not update the notam structure
    $job = new NotamRequestJob('EICK,EIDW');
    $job->handle();
    expect(Notam::count())->toBe(34);

    $updatedNotam = Notam::find('B1124/23-EIDW');
    expect($updatedNotam->source['Qcode'])->toContain('ZZZZ');
    expect($updatedNotam->code)->toBe('A1'); //Ensure notam tags have not been removed etc
    expect($updatedNotam->type)->toBe('Airport Issue');
    expect($updatedNotam->updated_at)->not()->toEqual($taggedNotam->updated_at); //Prove it was updated/touched.
});
