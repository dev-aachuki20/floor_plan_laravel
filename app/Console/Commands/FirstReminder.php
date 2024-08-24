<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RotaSession;
use Illuminate\Console\Command;
use App\Notifications\SendNotification;
use Symfony\Component\HttpFoundation\Response;


class FirstReminder extends Command
{
    protected $signature = 'reminder:first';
    protected $description = 'Send first reminder for session confirmation';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $targetDate = Carbon::now()->addWeeks(5);
        $sessions = RotaSession::whereDate('session_day', '=', $targetDate->toDateString())
        ->where('confirmed', false)
        ->get();

        $nextWeekStart = $now->copy()->startOfWeek()->addWeek()->startOfDay();
        $oneDayBeforeNextWeekStart = $nextWeekStart->copy()->subDay();
 
        if (Carbon::today()->equalTo($oneDayBeforeNextWeekStart)) {

            $currentWeekNo = $nextWeekStart->weekOfYear;

            $unconfirmedSessions = RotaSession::whereNotNull('quarter_id')
            ->whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->where('week_no',$currentWeekNo)
            ->whereBetween('week_day_date', [$nextWeekStart->format('Y-m-d'), $nextWeekStart->copy()->endOfWeek()->format('Y-m-d')])
            ->get();

            $usersToNotify = [];

            foreach ($unconfirmedSessions as $key=>$session) {


                //Speciality Lead User
                $speciality_lead_user = $session->users()->wherePivot('status','!=',1)->wherePivot('role_id',config('constant.roles.speciality_lead'))->first();
                if($speciality_lead_user){

                    $usersToNotify[$session->id][] = $speciality_lead_user->id;
                    
                }else{
                    
                    $specialityLeadUsers = $session->specialityDetail->users()->where('primary_role',config('constant.roles.speciality_lead'))->get();

                    foreach ($specialityLeadUsers as $user) {
                        $usersToNotify[$session->id][] = $user->id;
                    }
                }

                //Anesthetic lead
                $anesthetic_lead_user = $session->users()->wherePivot('status','!=',1)->wherePivot('role_id',config('constant.roles.anesthetic_lead'))->first();
                if($anesthetic_lead_user){

                    $usersToNotify[$session->id][] = $anesthetic_lead_user->id;

                }else{
                    
                    $anestheticLeadUsers = User::where('primary_role',config('constant.roles.anesthetic_lead'))->get();

                    foreach ($anestheticLeadUsers as $user) {
                        $usersToNotify[$session->id][] = $user->id;
                    }
                }

                //Staff Coordinator
                $staff_user = $session->users()->wherePivot('status','!=',1)->wherePivot('role_id',config('constant.roles.staff_coordinator'))->first();
                if($staff_user){

                    $usersToNotify[$session->id][] = $staff_user->id;

                }else{
                    
                    $staffUsers = User::where('primary_role',config('constant.roles.anesthetic_lead'))->get();

                    foreach ($staffUsers as $user) {
                        $usersToNotify[$session->id][] = $user->id;
                    }
                }

                $usersToNotify[$session->id] = array_unique($usersToNotify[$session->id]);

            }
            
            // Send notifications
            foreach ($usersToNotify as $sessionId => $allUsersId) {

                $rotaSession = RotaSession::find($sessionId);

                $allUsers = User::whereIn('id',$allUsersId)->get();
                foreach($allUsers as $user){
                    $subject = 'Reminder : '.trans('messages.notification_subject.available');

                    $notification_type = array_search(config('constant.notification_type.session_available'), config('constant.notification_type'));

                    $messageContent = $rotaSession->roomDetail->room_name.' - '. $rotaSession->specialityDetail->rotaSession;

                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                    $messageData = [
                        'notification_type' => $notification_type,
                        'section'           => $key,
                        'subject'           => $subject,
                        'message'           => $messageContent,
                        'rota_session'      => $rotaSession,
                        'created_by'        => config('constant.roles.system_admin')
                    ];

                    $user->notify(new SendNotification($messageData));
                }

            }

            $this->info('Notifications for unconfirmed sessions have been sent.');

        }
      

    }
}
