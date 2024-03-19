<?php

use App\Enum\Airports;
use App\Jobs\NotamRequestJob;
use Illuminate\Support\Facades\Schedule;

//For demo purposes we are only getting notams for irish, uk, Aus and NZ airports.
//In real life we would connect to an api that would provide all
//new notams for all world airports.
Schedule::job(new NotamRequestJob(Airports::BATCH1))
    ->hourlyAt(['07']);

Schedule::job(new NotamRequestJob(Airports::BATCH2))
    ->hourlyAt(['13']);

Schedule::job(new NotamRequestJob(Airports::BATCH3))
    ->hourlyAt(['19']);

Schedule::job(new NotamRequestJob(Airports::BATCH4))
    ->hourlyAt(['25']);

Schedule::job(new NotamRequestJob(Airports::BATCH5))
    ->hourlyAt(['31']);

Schedule::job(new NotamRequestJob(Airports::BATCH6))
    ->hourlyAt(['37']);

Schedule::job(new NotamRequestJob(Airports::BATCH7))
    ->hourlyAt(['43']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_1))
    ->hourlyAt(['45']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_2))
    ->hourlyAt(['48']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_3))
    ->hourlyAt(['51']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_4))
    ->hourlyAt(['54']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_5))
    ->hourlyAt(['57']);

Schedule::job(new NotamRequestJob(Airports::BATCH_FIRS_6))
    ->hourlyAt(['01']);
