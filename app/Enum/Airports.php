<?php

namespace App\Enum;

//Yes yes I know this isn't an enum, but I'm against the clock.
class Airports
{
    const BATCH1 = 'EIDW,EICK,EGAD,EGAC,EGBB,EGTE';

    const BATCH2 = 'EINN,EIKY,EIKN,EGAA,EGAE,EGGD,EGNM';

    const BATCH3 = 'EGNX,EGGW,EGSS,EGLL,EGLC,EGNT';

    const BATCH4 = 'EGPH,EGPF,EGPK,EGFF,EGGP,EGCC,EGKK';

    const BATCH5 = 'YSCB,YSSY,YPAD,YPPH,YPDN';

    const BATCH6 = 'YBBN,YBCS,YBCG,YMML,YBRM';

    const BATCH7 = 'NZAA,NZCH,NZWN,NZQN,NZDN,NZNS';

    const BATCH_FIRS_1 = 'EISN,EGGX,EGTT';

    const BATCH_FIRS_2 = 'YBBB,YMMM';

    const BATCH_FIRS_3 = 'NZZO,NZZC';

    const ALL = self::BATCH1.','.self::BATCH2.','.self::BATCH3.','.self::BATCH4.','.self::BATCH5.','.self::BATCH6.','.self::BATCH7.','.self::BATCH_FIRS_1.','.self::BATCH_FIRS_2.','.self::BATCH_FIRS_3;
}
