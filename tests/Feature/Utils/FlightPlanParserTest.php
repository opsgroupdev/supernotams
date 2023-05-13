<?php

use App\Utils\FlightPlanParser;

function plan1(): string
{
    return <<<EOL
FF EUCHZMFP EUCBZMFP EIDWEINU
       EIDWEINU
AD EGGXZOZX BIRDZPZZ BIRDCPRX CZQXZQZX CZULZQZX CZQMZQZX CZQMZQZR
(FPL-EIN1LW-IS
-A332/H-SDE2E3FGHIJ4J5M1P2RWXYZ/LB1D1
-EIDW1310
-N0446F300 SUROX3J SUROX/N0446F300 DCT REVNU/N0442F300 DCT
 SUNOT/M075F300 DCT 59N020W 62N030W 64N040W 63N050W DCT
 MAXAR/M081F380 DCT LAKES/N0469F400 DCT TAFFY DCT PQI J55 HTO J174
 ORF J121 CHS DCT IGARY Q85 LPERD SNFLD3
-KMCO0926 KTPA
-PBN/A1B1D1L1S1 NAV/RNP2 DAT/1FANS2PDC CPDLCX SUR/RSP180 260B
 DOF/230329 REG/EIDUO EET/EGGX0026 SUNOT0055 59N020W0121 BIRD0150
 62N030W0207 BGGL0243 64N040W0249 63N050W0330 CZQX0347 MAXAR0405
 CZUL0427 CZQX0443 CZUL0454 CZQX0504 CZUL0515 CZQM0605 KZBW0613
 KZDC0724 KZJX0826 SEL/FLJR CODE/4CA5C6 OPR/EIN PER/C TALT/EGAA
 RVR/075 RMK/TCAS AER LINGUS OPERATIONS 0035318862147)
EOL;
}
function plan2(): string
{
    return <<<EOL
FF EUCHZMFP EUCBZMFP EIDWEINU
  EIDWEINU
  AD EGGXZOZX GMACZQZX GMMMZQZX GMMMZFZX
  (FPL-EIN78L-IS
  -A320/M-SDE2E3FGHIJ1RWXYZ/HB1
  -EIDW1330
  -N0445F360 PELIG6A PELIG DCT MAPAG/M078F360 DCT LASNO T9
  BEGAS/N0445F350 DCT DEMOS DCT ORSOS DCT BEXAL UN866 KONBA
  -GCLP0415 GCFV
  -PBN/A1B1D1L1S2 NAV/RNP2 DOF/221107 REG/EIDEH EET/EGGX0032
  LASNO0056 LECM0130 LPPC0159 GMMM0253 GCCC0342 SEL/AEJR CODE/4CA216
  OPR/EIN PER/C RVR/075 RMK/TCAS AER LINGUS OPERATIONS
  0035318862147)
EOL;
}

it('parses an ATC flightplan', function () {
    $result = FlightPlanParser::process(plan1());

    expect($result['departureAirport'])->toBe("EIDW");
    expect($result['destinationAirport'])->toBe("KMCO");
    expect($result['destinationAlternates'])->toMatchArray(['KTPA']);
    expect($result['firs'])->toMatchArray(['EGGX','BIRD','BGGL','CZQX','CZUL','CZQM','KZBW','KZDC','KZJX']);
    expect($result['enrouteAlternates'])->toMatchArray([]);
    expect($result['takeoffAlternate'])->toBe('EGAA');
});

it('parses another ATC flightplan', function () {
    $result = FlightPlanParser::process(plan2());

    expect($result['departureAirport'])->toBe("EIDW");
    expect($result['destinationAirport'])->toBe("GCLP");
    expect($result['destinationAlternates'])->toMatchArray(['GCFV']);
    expect($result['firs'])->toMatchArray(['EGGX','LECM','LPPC','GMMM','GCCC']);
    expect($result['enrouteAlternates'])->toMatchArray([]);
    expect($result['takeoffAlternate'])->toBeNull();
});