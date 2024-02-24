<?php

namespace App\Console;

use App\Enum\Airports;
use App\Jobs\NotamRequestJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //For demo purposes we are only getting notams for irish and uk airports.
        //In real life we would connect to an api that would provide all
        //new notams for all world airports.
        $schedule
            ->job(new NotamRequestJob(Airports::BATCH1))
            ->hourlyAt(['07']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH2))
            ->hourlyAt(['13']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH3))
            ->hourlyAt(['19']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH4))
            ->hourlyAt(['25']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH5))
            ->hourlyAt(['31']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH6))
            ->hourlyAt(['37']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH7))
            ->hourlyAt(['43']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_1))
            ->hourlyAt(['45']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_2))
            ->hourlyAt(['48']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_3))
            ->hourlyAt(['51']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_4))
            ->hourlyAt(['54']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_5))
            ->hourlyAt(['57']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH_FIRS_6))
            ->hourlyAt(['01']);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
