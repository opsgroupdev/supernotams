<?php

use App\Actions\NotamRetriever;
use App\Actions\OpenAITagger;
use App\Events\NotamProcessingEvent;
use App\Jobs\NotamProcessingJob;
use Illuminate\Support\Facades\Event;

function atcPlan1()
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

it('runs the whole process', function () {
    Event::fake([NotamProcessingEvent::class]);

    $this->mock(NotamRetriever::class)
        ->expects('icaoSource')
        ->andReturn(collect(json_decode(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')), true)));

    $this->mock(OpenAITagger::class)
        ->expects('process')
        ->andReturn(collect(json_decode(file_get_contents(base_path('tests/source/ICAO_NotamsWithTags.json')), true)));

    NotamProcessingJob::dispatch(atcPlan1(), 'mychannel');
});
