<?php
namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Room;
use App\Models\Hospital;
use App\Models\RotaSession;
use App\Models\RotaSessionQuarter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Notifications\SendNotification;


class SetQuarterDays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $quarterId;
    protected $quarterYear;
    protected $hospitalId;
    protected $remainingDays;
    protected $created_by;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->quarterId     = $data['quarter_id'];
        $this->quarterYear   = $data['quarter_year'];
        $this->hospitalId    = $data['hospital_id'];
        $this->remainingDays = $data['remaining_days'];
        $this->created_by    = $data['created_by'];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            DB::beginTransaction();

            $newQuarterSet = false;
            $allUsers = [];

            $rooms = Room::select('id', 'room_name')->where('hospital_id',$this->hospitalId)->get();
            $timeSlots = config('constant.time_slots');
            foreach ($rooms as $room) {
                foreach ($timeSlots as $timeSlot) {

                    foreach ($this->remainingDays as $date) {

                        $start = Carbon::parse($date);
                        $dayOfWeek = $start->format('l');
                        $weekNumber = $start->weekOfYear;

                        $quarterWeek = RotaSessionQuarter::where('quarter_no', $this->quarterId)
                        ->where('quarter_year', $this->quarterYear)
                        ->where('hospital_id', $this->hospitalId)
                        ->where('room_id', $room->id)
                        ->where('time_slot', $timeSlot)
                        ->where('day_name', $dayOfWeek)
                        ->first();

                        if ($quarterWeek) {

                            $isSpecialityChanged = false;
                            $isNewCreated = false;

                            // Check if the rota session already exists
                            $rotaSession = RotaSession::where('hospital_id',$this->hospitalId)
                            ->where('room_id', $room->id)
                            ->where('time_slot', $timeSlot)
                            ->where('week_day_date', $date)
                            ->first();

                            $rotaSessionRecords = [
                                'quarter_id'      => $this->quarterId ?? null,
                                'hospital_id'     => $this->hospitalId,
                                'week_no'         => $weekNumber,
                                'room_id'         => $room->id,
                                'time_slot'       => $timeSlot,
                                'speciality_id'   => $quarterWeek->speciality_id ?? config('constant.unavailable_speciality_id'),
                                'week_day_date'   => $date,
                                'created_by'      => $this->created_by,
                            ];


                            if ($rotaSession) {

                                if(!is_null($rotaSession->speciality_id)){

                                    if($rotaSession->speciality_id != $rotaSessionRecords['speciality_id']){
                                        $isSpecialityChanged = true;

                                        $speciality_name_before_changed = $rotaSession->specialityDetail->speciality_name;
                                    }

                                }else{
                                    $isNewCreated = true;
                                }
                                // Update existing rota session
                                $rotaSession->update($rotaSessionRecords);

                                $rotaSession = RotaSession::find($rotaSession->id);

                            } else {
                                // Create new rota session
                                $rotaSession = RotaSession::create($rotaSessionRecords);

                                $isNewCreated = true;
                                // $newQuarterSet = true;
                            }


                            // Send notification to confirm user if speciality changed
                            $rolesId = [
                                config('constant.roles.speciality_lead'),
                                config('constant.roles.staff_coordinator'),
                                config('constant.roles.anesthetic_lead'),
                            ];

                            $createdBy = User::where('primary_role',config('constant.roles.system_admin'))->first();

                            if($isSpecialityChanged){

                                //If Speciality changed than send notification to all confirm user that session has been cancelled
                                $existingConfirmedUsers = $rotaSession->users()->wherePivot('status', 1)->wherePivotIn('role_id', $rolesId)->get();

                                foreach($existingConfirmedUsers as $user){

                                    $subject = trans('messages.notify_subject.remove_speciality');

                                    $notification_type = array_search(config('constant.notification_type.session_cancelled'), config('constant.notification_type'));

                                    $messageContent = $rotaSession->hospitalDetail->hospital_name.' - '.$rotaSession->roomDetail->room_name;

                                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                                    $messageData = [
                                        'notification_type' => $notification_type,
                                        'section'           => $key,
                                        'subject'           => $subject,
                                        'message'           => $messageContent,
                                        'rota_session'      => $rotaSession,
                                        'created_by'        => $createdBy->id
                                    ];

                                    $user->notify(new SendNotification($messageData));
                                }

                                $rotaSession->users()->sync([]);
                            }
                            //End send notification to confirm user if speciality changed

                            if($rotaSession->speciality_id != config('constant.unavailable_speciality_id')){

                                $hospital_id = $this->hospitalId;

                                //Send notification for session confirmation to speciality lead user
                                $specialityUsers = $rotaSession->specialityDetail ? $rotaSession->specialityDetail->users()->where('primary_role', config('constant.roles.speciality_lead'))->whereHas('getHospitals', function ($query) use($hospital_id) {
                                    $query->where('hospital_id', $hospital_id);
                                })->get() : [];

                                foreach ($specialityUsers as $user) {

                                    $allUsers[$user->id][] = $rotaSession->id;

                                    /*if($isNewCreated || $isSpecialityChanged){

                                        $subject = trans('messages.notify_subject.confirmation');

                                        $notification_type = array_search(config('constant.notification_type.session_available'), config('constant.notification_type'));

                                        $messageContent = $rotaSession->hospitalDetail->hospital_name.' - '.$rotaSession->roomDetail->room_name;

                                        $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                                        $messageData = [
                                            'notification_type' => $notification_type,
                                            'section'           => $key,
                                            'subject'           => $subject,
                                            'message'           => $messageContent,
                                            'rota_session'      => $rotaSession,
                                            'created_by'        => $createdBy->id
                                        ];

                                        $user->notify(new SendNotification($messageData));
                                    }*/
                                }
                                //End send notification for session confirmation to speciality lead user


                                //Send notification for session confirmation to anesthetic lead & staff coordinator
                                $staffRoles = [
                                    config('constant.roles.staff_coordinator'),
                                    config('constant.roles.anesthetic_lead'),
                                ];
                                $staffUsers = User::whereIn('primary_role', $staffRoles)->whereHas('getHospitals', function ($query) use($hospital_id) {
                                    $query->where('hospital_id', $hospital_id);
                                })->get();

                                foreach ($staffUsers as $user) {

                                    $allUsers[$user->id][] = $rotaSession->id;

                                   /*if($isNewCreated || $isSpecialityChanged){

                                        $subject = trans('messages.notify_subject.confirmation');

                                        $notification_type = array_search(config('constant.notification_type.session_available'), config('constant.notification_type'));

                                        $messageContent = $rotaSession->hospitalDetail->hospital_name.' - '.$rotaSession->roomDetail->room_name;

                                        $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                                        $messageData = [
                                            'notification_type' => $notification_type,
                                            'section'           => $key,
                                            'subject'           => $subject,
                                            'message'           => $messageContent,
                                            'rota_session'      => $rotaSession,
                                            'created_by'        => $createdBy->id
                                        ];

                                        $user->notify(new SendNotification($messageData));
                                    }*/
                                }
                                //End send notification for session confirmation to anesthetic lead & staff coordinator
                            }

                        }

                    }

                }
            }

            //Send notification as quarter is set
            // if($newQuarterSet){

                $userIds = array_keys($allUsers);

                $hospital_id = $this->hospitalId;
                
                //Send notification to speciality lead, anesthetic lead & staff coordinator
                $staffUsers = User::whereIn('id', $userIds)->whereHas('getHospitals', function ($query) use($hospital_id) {
                    $query->where('hospital_id', $hospital_id);
                })->get();

                foreach ($staffUsers as $user) {

                    $subject = trans('messages.notify_subject.quarter_available',['quarterNo'=>$this->quarterId,'quarterYear' => $this->quarterYear]);

                    $notification_type = array_search(config('constant.notification_type.quarter_available'), config('constant.notification_type'));

                    $hospital = Hospital::where('id',$this->hospitalId)->first();

                    $messageContent = $hospital->hospital_name;

                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                    $messageData = [
                        'notification_type' => $notification_type,
                        'section'           => $key,
                        'subject'           => $subject,
                        'message'           => $messageContent,
                        'rota_session'      => null,
                        'created_by'        => $this->created_by,
                        'hospital_id'       => $hospital->id,
                        'hospital_name'     => $hospital->hospital_name,
                        'rota_session_ids'  => isset($allUsers[$user->id]) ? $allUsers[$user->id] : null,
                        'session_date'      => isset($this->remainingDays[0]) ? $this->remainingDays[0] : null,
                    ];

                    $user->notify(new SendNotification($messageData));

                }
                //End send notification to speciality lead, anesthetic lead & staff coordinator
            // }
            //End send notification as quarter is set


            //Notify admin user
            $hospital = Hospital::find($this->hospitalId);
            $adminUsers = $hospital->users()->whereIn('primary_role',[config('constant.roles.trust_admin'),config('constant.roles.hospital_admin')])->select('id','full_name','user_email')->get();

            $superAdmin = User::where('primary_role', config('constant.roles.system_admin'))->select('id', 'full_name', 'user_email')->first();
            if ($superAdmin) {
                $adminUsers = $adminUsers->concat([$superAdmin]);
            }

            if($adminUsers){
                foreach($adminUsers as $user){

                    $subject = trans('messages.notify_subject.quarter_saved',['quarterNo'=>$this->quarterId,'quarterYear' => $this->quarterYear]);

                    $notification_type = array_search(config('constant.notification_type.quarter_saved'), config('constant.notification_type'));

                    $messageContent = $hospital->hospital_name;

                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                    $messageData = [
                        'notification_type' => $notification_type,
                        'section'           => $key,
                        'subject'           => $subject,
                        'message'           => $messageContent,
                        'rota_session'      => null,
                        'quarterNo'         => $this->quarterId,
                        'quarterYear'       => $this->quarterYear,
                        'hospital_id'       => $hospital->id,
                        'session_date'      => isset($this->remainingDays[0]) ? $this->remainingDays[0] : null,
                    ];

                    $user->notify(new SendNotification($messageData));
                }

            }

            DB::commit();

        }catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to process rota session: ' . $e->getMessage());

            throw $e;
        }

    }
}
