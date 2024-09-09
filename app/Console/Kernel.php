<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        \Log::info('Schedule cron job is running');

        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

        // Schedule the reminder 5 weeks before the session date 
        $schedule->command('notify:reminder five_weeks')->daily()->withoutOverlapping();

        // Schedule the reminder 4 weeks before the session date 
        $schedule->command('notify:reminder four_weeks')->daily()->withoutOverlapping();

        // Schedule the reminder 2 weeks before the session date 
        $schedule->command('notify:reminder two_weeks')->daily()->withoutOverlapping();

        $schedule->command('check:backup-speciality-confirmation')->daily()->withoutOverlapping();

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
