<?php

use App\Events\NotamProcessingEvent;
use App\Events\PdfResultEvent;
use App\Jobs\NotamProcessingJob;
use App\Models\Notam;
use Illuminate\Support\Facades\Event;

function plan_dub_lhr()
{
    return <<<'EOL'
FF EUCHZMFP EUCBZMFP EIDWEINU
       EIDWEINU
(FPL-EIN154-IS
-A21N/M-SDE2E3FGHIJ1J4J7M3P2RWXYZ/LB1D1
-EIDW0740
-N0434F330 ENDEQ1A ENDEQ Q36 NUGRA NUGRA1H
-EGLL0041 EGPK
-PBN/A1B1D1L1S2 NAV/RNP2 DAT/1FANS2PDC SUR/RSP180 260B DOF/221201
 REG/EILRB EET/EGTT0008 SEL/FPGQ CODE/4CA9BB OPR/EIN PER/C RVR/075
 RMK/TCAS AER LINGUS OPERATIONS 0035318862147)
EOL;
}

function plan_bel_ork()
{
    return <<<'EOL'
FF KZDCZQZX EUCHZMFP EUCBZMFP EIDWEINU CZQMZQZX CZQMZQZR CZQXZQZX
  EGGXZOZX
  EIDWEINU
  (FPL-EIN118-IS
  -A21N/M-SDE2E3FGHIJ1J4J7M3P2RWXYZ/LB1D1
  -EGAA0055
  -N0451F330 JCOBY4 SWANN DCT BROSS Q419 RBV DCT LLUND DCT BAYYS DCT
  PUT DCT TUSKY N201B NICSO/M078F330 DCT 48N050W 51N040W 52N030W
  53N020W DCT MALOT/N0453F330 DCT GISTI DCT OSGAR OSGAR3X
  -EICK0617 EINN EIDW
  -PBN/A1B1D1L1S2 NAV/RNP2 DAT/1FANS2PDC SUR/RSP180 260B DOF/230510
  REG/EILRG EET/EGGX0425 53N020W0510 EISN0532 SEL/AGEJ
  CODE/4CABD3 OPR/EIN PER/C RALT/EIKN RVR/075 RMK/TCAS AER
  LINGUS OPERATIONS 0035318862147)
EOL;
}

it('can generate a proper notam briefing document', function () {
    Event::fake();
    Storage::fake('local');

    //Recreate a database of Notams.
    Notam::insert(File::json(base_path('tests/source/tagged_notams.json')));
    expect(Notam::all())->toHaveCount(314);

    //Generate a pretend response from the notam fetcher as if we had asked for specific locations
    $rawNotamJson = collect(File::json(base_path('tests/source/tagged_notams.json')))->pluck('source');
    Http::fakeSequence()->push(str($rawNotamJson->implode(','))->wrap('[', ']')->value());

    //Run the entire job/process
    NotamProcessingJob::dispatchSync(plan_dub_lhr(), 'x10WI4RqH1t68qHIsLYzMteUVbogJX17foA5aNnR');

    Event::assertDispatchedTimes(NotamProcessingEvent::class, 5);
    Event::assertDispatchedTimes(PdfResultEvent::class);

    Event::assertDispatched(function (PdfResultEvent $event) {
        $pdf = Cache::get($event->key);
        Storage::disk('local')->put('NotamPack.pdf', $pdf);

        return str($pdf)->startsWith('%PDF-');
    });

    Event::assertDispatched(function (NotamProcessingEvent $event) {
        return str($event->message)->contains('Just received 314 valid notams!');
    });
});

it('sends error messages to the user', function () {
    Event::fake();

    NotamProcessingJob::dispatchSync('BAD FLIGHTPLAN', 'x10WI4RqH1t68qHIsLYzMteUVbogJX17foA5aNnR');

    Event::assertNotDispatched(PdfResultEvent::class);
    Event::assertDispatched(function (NotamProcessingEvent $event) {
        return str($event->message)->contains('Sorry unable to extract all the required details from the flight plan supplied. Please try again.');
    });
});
