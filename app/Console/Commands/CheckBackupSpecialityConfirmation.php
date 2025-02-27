<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use App\Models\BackupSpeciality;
use Illuminate\Console\Command;
use App\Notifications\SendNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RotaSessionClosedToAdmins;

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

       $backupSpecialities = BackupSpeciality::get();

       foreach($backupSpecialities as $backupSpeciality){
            if ($backupSpeciality->speciality) {

                $backupSpecialityLeadUsers = $backupSpeciality->speciality->users()->where('primary_role',config('constant.roles.speciality_lead'))->pluck('id');

                $backupSpecialityLeadUsers = $backupSpecialityLeadUsers->count() > 0 ? $backupSpecialityLeadUsers->toArray() : [];

                $days = getSetting('session_closed') ? (int)getSetting('session_closed') : 7;
                
                $rotaSessions = RotaSession::whereNotNull('speciality_id')
                ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
                ->whereDate('week_day_date', $currentDate->addDays($days))
                ->where('hospital_id',$backupSpeciality->hospital_id)
                ->where('status', 2)
                ->get();

                foreach ($rotaSessions as $session) {

                    $existingRecordWithStatusOne = $session->users()
                    ->wherePivot('role_id', config('constant.roles.speciality_lead'))
                    ->wherePivotIn('user_id', $backupSpecialityLeadUsers)
                    ->wherePivot('status', 1)
                    ->first();

                    if( (!$existingRecordWithStatusOne) || $this->staffUnavailablityStatus($session)){

                        $hospital_id = $session->hospital_id;

                        $session->status = config('constant.session_status.failed');
                        $session->save();

                        $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();

                        $subject = trans('messages.notify_subject.session_failed');
                        $notificationType = array_search(config('constant.notification_type.session_failed'), config('constant.notification_type'));
                        $messageContent = $session->hospitalDetail->hospital_name.' - '.$session->roomDetail->room_name;
                        $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                        $messageData = [
                            'notification_type' => $notificationType,
                            'section'           => $key,
                            'subject'           => $subject,
                            'message'           => $messageContent,
                            'rota_session'      => $session,
                            'created_by'        => $createdBy->id
                        ];


                        $backupSpecialityUsers = User::whereIn('id',$backupSpecialityLeadUsers)->whereHas('getHospitals', function ($query) use($hospital_id) {
                            $query->where('hospital_id', $hospital_id);
                        })->get();

                        foreach($backupSpecialityUsers as $user){
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
                            $user->notify(new SendNotification($messageData));
                        }
                        //End send notification for session confirmation to anesthetic lead & staff coordinator

                    }

                }

                if($rotaSessions->count() > 0){

                    //Send mail to admin roles
                    $subject = trans('messages.notify_subject.session_failed');

                    $hospitalName = $backupSpeciality->hospitalDetail ? $backupSpeciality->hospitalDetail->hospital_name : null;

                    $adminRoles = [
                        config('constant.roles.hospital_admin'),
                        config('constant.roles.chair'),
                    ];
                    $adminUsers = User::whereIn('primary_role', $adminRoles)->whereHas('getHospitals', function ($query) use($backupSpeciality) {
                        $query->where('hospital_id', $backupSpeciality->hospital_id);
                    })->get();
                    foreach ($adminUsers as $user) {
                        Mail::to($user->user_email)->queue(new RotaSessionClosedToAdmins($subject, $user, $hospitalName ,$rotaSessions));
                    }

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
