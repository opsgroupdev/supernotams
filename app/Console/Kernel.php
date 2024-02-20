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
            ->hourlyAt(['07', '37']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH2))
            ->hourlyAt(['14', '44']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH3))
            ->hourlyAt(['21', '51']);

        $schedule
            ->job(new NotamRequestJob(Airports::BATCH4))
            ->hourlyAt(['28', '58']);
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
