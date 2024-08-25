<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use Illuminate\Console\Command;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Log;

class ReminderNotification extends Command
{
    protected $signature = 'notify:reminder {weeks}';
    protected $description = 'Send reminder for session';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $weeks = $this->argument('weeks');

        switch ($weeks) {
            case 'five_weeks':
                $dateThreshold = Carbon::now()->addWeeks(5)->format('Y-m-d');
                break;
            case 'four_weeks':
                $dateThreshold = Carbon::now()->addWeeks(4)->format('Y-m-d');
                break;
            case 'two_weeks':
                $dateThreshold = Carbon::now()->addWeeks(2)->format('Y-m-d');
                break;
            default:
                $this->error('Invalid weeks parameter provided.');
                return 1;
        }

        $rotaSessions = RotaSession::whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->where('week_day_date', $dateThreshold)
            ->get();

        foreach ($rotaSessions as $session) {
            $this->checkAndSendNotifications($session, 'speciality_lead', $weeks);
            $this->checkAndSendNotifications($session, 'anesthetic_lead', $weeks);
            $this->checkAndSendNotifications($session, 'staff_coordinator', $weeks);

            if($weeks == 'two_weeks'){
                $session->status = config('constant.session_status.closed');
                $session->save();
            } elseif ($this->isSessionAtRisk($session)) {
                $session->status = config('constant.session_status.at_risk');
                $session->save();
            }

        }

        if ($rotaSessions->count() > 0) {
            Log::info("Reminder sent for sessions {$weeks} before.");
            $this->info("Reminder sent for sessions {$weeks} before.");
        }

        return 0;
    }

    private function checkAndSendNotifications($session, $role, $weeks)
    {
        $roleConstant = config("constant.roles.$role");

        $confirmedUserExists = $session->users()
            ->where('primary_role', $roleConstant)
            ->wherePivot('status', 1)
            ->wherePivot('role_id', $roleConstant)
            ->exists();

        if (!$confirmedUserExists) {
            $users = User::where('primary_role', $roleConstant)->get();
            foreach ($users as $user) {
                $this->sendNotification($session, $user,$weeks);
            }
        }
    }

    private function isSessionAtRisk($session)
    {
        $requiredRoles = ['speciality_lead', 'anesthetic_lead', 'staff_coordinator'];
        foreach ($requiredRoles as $role) {
            $roleConstant = config("constant.roles.$role");

            $confirmedUserExists = $session->users()
                ->where('primary_role', $roleConstant)
                ->wherePivot('status', 1)
                ->wherePivot('role_id', $roleConstant)
                ->exists();

            if (!$confirmedUserExists) {
                return true;
            }
        }

        return false;
    }

    private function sendNotification($rotaSession, $user, $weeks)
    {
        $subject = trans('messages.notify_subject.first_reminder');
        $notificationType = array_search(config('constant.notification_type.first_reminder'), config('constant.notification_type'));
        $messageContent = "{$rotaSession->hospitalDetail->hospital_name} - {$rotaSession->roomDetail->room_name}";

        if($weeks == 'four_weeks'){
            $subject = trans('messages.notify_subject.final_reminder');
            $notificationType = array_search(config('constant.notification_type.final_reminder'), config('constant.notification_type'));
        }

        if($weeks == 'two_weeks'){
            $subject = trans('messages.notify_subject.session_closed');
            $notificationType = array_search(config('constant.notification_type.session_closed'), config('constant.notification_type'));
        }

        $sectionKey = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

        $messageData = [
            'notification_type' => $notificationType,
            'section'           => $sectionKey,
            'subject'           => $subject,
            'message'           => $messageContent,
            'rota_session'      => $rotaSession,
            'created_by'        => config('constant.roles.system_admin')
        ];

        $user->notify(new SendNotification($messageData));
    }
}
