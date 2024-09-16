<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use App\Models\BackupSpeciality;
use Illuminate\Console\Command;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Log;

class ReminderNotification extends Command
{
    protected $signature = 'notify:reminder {type}';
    protected $description = 'Send reminder for session';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $type = $this->argument('type');

        Log::info('Start Sending Notfication for '.$type);
        
        $beforeDays = '';

        switch ($type) {
            case 'first_reminder':
                
                $beforeDays = getSetting('first_reminder') ? (int)getSetting('first_reminder') : 35;
                $dateThreshold = Carbon::now()->addDays($beforeDays)->format('Y-m-d');

                break;
            case 'final_reminder':

                $beforeDays = getSetting('final_reminder') ? (int)getSetting('final_reminder') : 28;
                $dateThreshold = Carbon::now()->addDays($beforeDays)->format('Y-m-d');

                break;
            case 'assign_backup_speciality':
               
                $beforeDays = getSetting('assign_backup_speciality') ? (int)getSetting('assign_backup_speciality') : 14;
                $dateThreshold = Carbon::now()->addDays($beforeDays)->format('Y-m-d');

                break;
            default:
                $this->error('Invalid type parameter provided.');
                return 1;
        }

        // dd($dateThreshold);
        
        $rotaSessions = RotaSession::whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->where('week_day_date', $dateThreshold)
            ->get();
    
        foreach ($rotaSessions as $session) {
            $this->checkAndSendNotifications($session, 'speciality_lead', $type);
            $this->checkAndSendNotifications($session, 'anesthetic_lead', $type);
            $this->checkAndSendNotifications($session, 'staff_coordinator', $type);
 
            if ($this->isSessionAtRisk($session)) {
                
                if($type == 'assign_backup_speciality'){
                    $session->status = config('constant.session_status.closed');
                
                    $this->assignToBackupSpeciality($session);
                }else{
                    $session->status = config('constant.session_status.at_risk');
                }

                $session->save();

            }

        }

        if ($rotaSessions->count() > 0) {
            Log::info("Reminder sent for sessions {$beforeDays} day before.");
            $this->info("Reminder sent for sessions {$beforeDays} day before.");
        }

        return 0;
    }

    private function checkAndSendNotifications($session, $role, $type)
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
                $this->sendNotification($session, $user,$type);
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

    private function sendNotification($rotaSession, $user, $type)
    {
        $subject = trans('messages.notify_subject.first_reminder');
        $notificationType = array_search(config('constant.notification_type.first_reminder'), config('constant.notification_type'));
        $messageContent = "{$rotaSession->hospitalDetail->hospital_name} - {$rotaSession->roomDetail->room_name}";

        if($type == 'final_reminder'){
            $subject = trans('messages.notify_subject.final_reminder');
            $notificationType = array_search(config('constant.notification_type.final_reminder'), config('constant.notification_type'));
        }

        if($type == 'assign_backup_speciality'){
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

        $backupSpeciality = BackupSpeciality::where('hospital_id',$hospital_id)->first();

        $backupSpecialityLeadUsers = $backupSpeciality->speciality->users()->where('primary_role',config('constant.roles.speciality_lead'))->pluck('id');

        if($backupSpecialityLeadUsers->count() > 0 ){

            foreach($backupSpecialityLeadUsers as $userId){

                $existingRecordWithStatusOne = $session->users()
                ->wherePivot('user_id', $userId)
                ->wherePivot('role_id', config('constant.roles.speciality_lead'))
                ->wherePivot('status', 1)
                ->first();
        
                if (!$existingRecordWithStatusOne) {

                    $existingRecord = $session->users()
                    ->wherePivot('user_id', $userId)
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
                                'user_id' => $userId,
                                'status'  => 0,
                            ]);
                        }

                    } else {
                        $availability_user[$userId] = ['role_id' => config('constant.roles.speciality_lead'),'status' => 0];
                        $session->users()->attach($userId, $availability_user[$userId]);
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

                    $user = User::where('id',$userId)->whereHas('getHospitals', function ($query) use($hospital_id) {
                        $query->where('hospital_id', $hospital_id);
                    })->first();

                    if($user){
                        $user->notify(new SendNotification($messageData));
                    }

                }
                
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
