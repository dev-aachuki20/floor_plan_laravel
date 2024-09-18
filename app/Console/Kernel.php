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

        // Schedule the first reminder before the session date with time 24 hour format
        $firstReminderTime = getSetting('first_reminder_time') ? getSetting('first_reminder_time') : '00:00';
        $schedule->command('notify:reminder first_reminder')->dailyAt($firstReminderTime)->withoutOverlapping();

        // Schedule the final reminder before the session date with time 24 hour format
        $finalReminderTime = getSetting('final_reminder_time') ? getSetting('final_reminder_time') : '00:00';
        $schedule->command('notify:reminder final_reminder')->dailyAt($finalReminderTime)->withoutOverlapping();

        // Schedule the assign backup speciality reminder before the session date with time 24 hour format
        $assignBackupTime = getSetting('assign_backup_speciality_time') ? getSetting('assign_backup_speciality_time') : '00:00';
        $schedule->command('notify:reminder assign_backup_speciality')->dailyAt($assignBackupTime)->withoutOverlapping();

        $sessionClosedTime = getSetting('session_closed_time') ? getSetting('session_closed_time') : '00:00';
        $schedule->command('check:backup-speciality-confirmation')->dailyAt($sessionClosedTime)->withoutOverlapping();

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
