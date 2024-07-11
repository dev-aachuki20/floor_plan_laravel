<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Notifications\SendNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class SendNotificationBeforeClockOutReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify-staff-after-shift-clockout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clock Out Reminder Notification Sent To Staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminderTime       = config('constant.notification_reminder.after_clock_out_shift');
        $section            = array_search(config('constant.notification_subject.announcements'), config('constant.notification_subject'));

        $now                = Carbon::now();
        
        /*$reminderEndTime  = $now->copy()->addMinutes($reminderTime)->format('H:i:00');

        $shifts = Shift::with(['staffs'])->where('status', 'picked')
        ->whereDate('start_date', '<=', $now)
        ->whereDate('end_date', '>=', $now)
        ->whereTime('end_time', '=', $reminderEndTime)
        ->whereHas('clockInOuts', function($q) use($now){
            $q->whereDate('clockin_date', $now)->whereNull('clockout_date');
        })
        ->get();*/
        
        $reminderEndTime = $now->copy()->subMinutes($reminderTime)->format('H:i:00');
        
        $shifts = Shift::with(['staffs'])
            ->where('status', 'picked')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->whereTime('end_time', '=', $reminderEndTime)
            ->whereHas('clockInOuts', function($q) use($now) {
                $q->whereDate('clockin_date', $now)
                  ->whereNull('clockout_date');
            })
            ->get();

        $allStaffs = $shifts->flatMap(function ($shift) {
            return $shift->staffs;
        })->unique('id');

        $messageData = [
            'notification_type' => array_search(config('constant.subject_notification_type.clock_out_reminder'), config('constant.subject_notification_type')),
            'section'           => $section,
            'subject'           => trans('messages.shift.shift_clock_out_reminder_subject'),
            'message'           => trans('messages.shift.shift_clock_out_reminder_message'),
            'task_type'         => 'cron'
        ];
        
        Notification::send($allStaffs, new SendNotification($messageData));

        $this->info('Clock Out Reminder Notification Sent To Staff');
    }
}
