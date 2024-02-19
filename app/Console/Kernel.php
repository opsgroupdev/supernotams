<?php

namespace App\Console;

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
        //For demo purposes we are only getting notams for irish airports.
        //In real life we would connect to an api that would provide all
        //new notams for all world airports.
        $airportsA = 'eidw,eick,eiwt,eicm,eiwf,egad,egac';
        $airportsB = 'einn,eiky,eikn,eisg,eidl,egaa,egae';

        $schedule
            ->job(new NotamRequestJob($airportsA))
            ->hourlyAt(['07', '37']);

        $schedule
            ->job(new NotamRequestJob($airportsB))
            ->hourlyAt(['22', '52']);
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
