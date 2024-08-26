<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use App\Models\BackupSpeciality;
use Illuminate\Console\Command;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Log;

class CheckBackupSpecialityConfirmation extends Command
{
    protected $signature = 'check:backup-speciality-confirmation';
    protected $description = 'Check if backup speciality confirms within allowed days and close the session if not';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currentDate = Carbon::now();

        $backupSpecialities = BackupSpeciality::whereHas('user',function($query){
            $query->where('primary_role',config('constant.roles.speciality_lead'));
        })->get();

        foreach ($backupSpecialities as $backupSpeciality) {
            $userId = $backupSpeciality->user_id;
            $days = $backupSpeciality->days;

            $rotaSessions = RotaSession::whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->where('week_day_date', $currentDate->addDays($days))
            ->get();
            
            foreach ($rotaSessions as $session) {

                $existingRecordWithStatusOne = $session->users()
                ->wherePivot('role_id', config('constant.roles.speciality_lead'))
                ->wherePivot('user_id',$userId)
                ->wherePivot('status', 1)
                ->first();

                if( (!$existingRecordWithStatusOne) && staffUnavailablityStatus($session)){

                    $session->status = config('constant.session_status.failed');
                    $session->save();

                    $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();

                    $subject = trans('messages.notify_subject.session_failed');
                    $notificationType = array_search(config('constant.notification_type.session_failed'), config('constant.notification_type'));
                    $messageContent = $session->hospitalDetail->hospital_name.' - '.$session->roomDetail->room_name;
                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                    $messageData = [
                        'notification_type' => $notification_type,
                        'section'           => $key,
                        'subject'           => $subject,
                        'message'           => $messageContent,
                        'rota_session'      => $session,
                        'created_by'        => $createdBy->id
                    ];

                    
                    $backupSpecialityUser = User::where('id',$userId)->first();
                    $backupSpecialityUser->notify(new SendNotification($messageData));

                    //Send notification for session confirmation to anesthetic lead & staff coordinator
                    $staffRoles = [
                        config('constant.roles.staff_coordinator'),
                        config('constant.roles.anesthetic_lead'),
                    ];
                    $staffUsers = User::whereIn('primary_role', $staffRoles)->get();
                    foreach ($staffUsers as $user) {
                        $user->notify(new SendNotification($messageData));
                    }
                    //End send notification for session confirmation to anesthetic lead & staff coordinator

                }

            }


        }

        
        return 0;
    }


    private function staffUnavailablityStatus($session)
    {
        $requiredRoles = ['anesthetic_lead', 'staff_coordinator'];
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

  
}
