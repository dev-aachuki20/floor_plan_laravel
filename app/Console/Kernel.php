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

        // Schedule the first reminder before the session date 
        $schedule->command('notify:reminder first_reminder')->daily()->withoutOverlapping();

        // Schedule the final reminder before the session date 
        $schedule->command('notify:reminder final_reminder')->daily()->withoutOverlapping();

        // Schedule the assign backup speciality reminder before the session date 
        $schedule->command('notify:reminder assign_backup_speciality')->daily()->withoutOverlapping();

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
