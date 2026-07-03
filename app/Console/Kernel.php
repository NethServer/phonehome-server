<?php

namespace App\Console;

use App\Jobs\RefreshNethvoiceStatsMaterializedViews;
use App\Jobs\RefreshPhonehomeDashboardMaterializedViews;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('ip-geolocation:update')->daily();
        $schedule->job(new RefreshNethvoiceStatsMaterializedViews)->dailyAt('02:00')->withoutOverlapping();
        $schedule->job(new RefreshPhonehomeDashboardMaterializedViews)->dailyAt('02:15')->withoutOverlapping();
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
