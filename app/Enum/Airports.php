<?php

namespace App\Enum;

//Yes yes I know this isn't an enum, but I'm against the clock.
class Airports
{
    const BATCH1 = 'eidw,eick,eiwt,eicm,eiwf,egad,egac';

    const BATCH2 = 'einn,eiky,eikn,eisg,eidl,egaa,egae';

    const ALL = self::BATCH1.','.self::BATCH2;
}
