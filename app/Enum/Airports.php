<?php

namespace App\Enum;

//Yes yes I know this isn't an enum, but I'm against the clock.
class Airports
{
    const BATCH1 = 'EIDW,EICK,EGAD,EGAC,EGBB,EGTE';

    const BATCH2 = 'EINN,EIKY,EIKN,EGAA,EGAE,EGGD,EGNM';

    const BATCH3 = 'EGNX,EGGW,EGSS,EGLL,EGLC,EGNT';

    const BATCH4 = 'EGPH,EGPF,EGPK,EGFF,EGGP,EGCC,EGKK';

    const ALL = self::BATCH1.','.self::BATCH2.','.self::BATCH3.','.self::BATCH4;
}
