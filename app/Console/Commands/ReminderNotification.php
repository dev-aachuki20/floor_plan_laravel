<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use App\Models\BackupSpeciality;
use App\Models\Setting;
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
        // $setting = Setting::first();

        switch ($weeks) {
            case 'five_weeks':
                
                // $beforeDays = ($setting && $setting->session_at_risk) ? (int)$setting->session_at_risk : 35;
              
                $dateThreshold = Carbon::now()->addWeeks(5)->addDays(1)->format('Y-m-d');
                // $dateThreshold = Carbon::now()->addDays($beforeDays)->addDays(1)->format('Y-m-d');
                break;
            case 'four_weeks':
                // $beforeDays = ($setting && $setting->session_at_risk) ? (int)$setting->session_at_risk : 35;
              
                $dateThreshold = Carbon::now()->addWeeks(4)->addDays(1)->format('Y-m-d');
                break;
            case 'two_weeks':
                $dateThreshold = Carbon::now()->addWeeks(2)->addDays(1)->format('Y-m-d');
                break;
            default:
                $this->error('Invalid weeks parameter provided.');
                return 1;
        }

        dd($dateThreshold);
        
        $rotaSessions = RotaSession::whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->where('week_day_date', $dateThreshold)
            ->get();

        foreach ($rotaSessions as $session) {
            $this->checkAndSendNotifications($session, 'speciality_lead', $weeks);
            $this->checkAndSendNotifications($session, 'anesthetic_lead', $weeks);
            $this->checkAndSendNotifications($session, 'staff_coordinator', $weeks);

            if ($this->isSessionAtRisk($session)) {
                
                if($weeks == 'two_weeks'){
                    $session->status = config('constant.session_status.closed');
                    $this->assignToBackupSpeciality($session);
                }else{
                    $session->status = config('constant.session_status.at_risk');
                }

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
            $hospital_id = $session->hospital_id;
            $users = User::where('primary_role', $roleConstant)->whereHas('getHospitals', function ($query) use($hospital_id) {
                $query->where('hospital_id', $hospital_id);
            })->get();
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

        $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();

        $messageData = [
            'notification_type' => $notificationType,
            'section'           => $sectionKey,
            'subject'           => $subject,
            'message'           => $messageContent,
            'rota_session'      => $rotaSession,
            'created_by'        => $createdBy->id,
        ];

        $user->notify(new SendNotification($messageData));
    }

    private function assignToBackupSpeciality($session){

        $hospital_id = $session->hospital_id;

        $backupSpeciality = BackupSpeciality::whereHas('user',function($query){
            $query->where('primary_role',config('constant.roles.speciality_lead'));
        })->where('hospital_id',$hospital_id)->first();

        if($backupSpeciality){
            
            $existingRecordWithStatusOne = $session->users()
            ->wherePivot('role_id', config('constant.roles.speciality_lead'))
            ->wherePivot('status', 1)
            ->first();
    
            if (!$existingRecordWithStatusOne) {

                $existingRecord = $session->users()
                ->wherePivot('role_id', config('constant.roles.speciality_lead'))
                ->wherePivot('rota_session_id',$session->id)
                ->first();
        
                if ($existingRecord) {
        
                    if($existingRecord->pivot->status != 1){
                        $session->users()
                        ->newPivotStatement()
                        ->where('rota_session_id', $session->id)
                        ->where('role_id', config('constant.roles.speciality_lead'))
                        ->update([
                            'user_id' => $backupSpeciality->user_id,
                            'status'  => 0,
                        ]);
                    }

                } else {
                    $availability_user[$backupSpeciality->user_id] = ['role_id' => config('constant.roles.speciality_lead'),'status' => 0];
                    $session->users()->attach($backupSpeciality->user_id, $availability_user[$backupSpeciality->user_id]);
                }


                //Send notification to backup speciality lead
                $subject = trans('messages.notify_subject.confirmation');
                $notification_type = array_search(config('constant.notification_type.session_available'), config('constant.notification_type'));
                $messageContent = $session->hospitalDetail->hospital_name.' - '.$session->roomDetail->room_name;

                $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();

                $messageData = [
                    'notification_type' => $notification_type,
                    'section'           => $key,
                    'subject'           => $subject,
                    'message'           => $messageContent,
                    'rota_session'      => $session,
                    'created_by'        => $createdBy->id
                ];

                $user = User::where('id',$backupSpeciality->user_id)->whereHas('getHospitals', function ($query) use($hospital_id) {
                    $query->where('hospital_id', $hospital_id);
                })->first();

                $user->notify(new SendNotification($messageData));

            }


            //Send notification for session confirmation to anesthetic lead & staff coordinator
            $staffRoles = [
                config('constant.roles.staff_coordinator'),
                config('constant.roles.anesthetic_lead'),
            ];

            $staffUsers = User::whereIn('primary_role', $staffRoles)->whereHas('getHospitals', function ($query) use($hospital_id) {
                $query->where('hospital_id', $hospital_id);
            })->get();
            
            foreach ($staffUsers as $user) {

                $subject = trans('messages.notify_subject.confirmation');

                $notification_type = array_search(config('constant.notification_type.session_available'), config('constant.notification_type'));

                $messageContent = $session->hospitalDetail->hospital_name.' - '.$session->roomDetail->room_name;

                $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();
                $messageData = [
                    'notification_type' => $notification_type,
                    'section'           => $key,
                    'subject'           => $subject,
                    'message'           => $messageContent,
                    'rota_session'      => $session,
                    'created_by'        => $createdBy->id
                ];

                $user->notify(new SendNotification($messageData));
                
            }
            //End send notification for session confirmation to anesthetic lead & staff coordinator
            
        }

    }
}
