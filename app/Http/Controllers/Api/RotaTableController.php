<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\Room;
use App\Models\Rota;
use App\Models\RotaSession;
use App\Models\RotaSessionQuarter;
use App\Models\BackupSpeciality;
use App\Models\Quarter;
use App\Models\Speciality;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Mail\RotaSessionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RotaTable\SaveRotaRequest;
use App\Http\Requests\RotaTable\UpdateAvailablityRequest;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;
use App\Notifications\SendNotification;
use App\Jobs\ProcessRemainingQuarterDays;
use App\Jobs\SetQuarterDays;


class RotaTableController extends APIController
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
            'filter_value'   => 'nullable|array',
            'filter_value.*' => 'integer',
            'time_slot'      => 'nullable',
        ]);

        try {

            $authUser  = auth()->user();

            $timeSlots = config('constant.time_slots');

            $adminRoles = [
                config('constant.roles.system_admin'),
                config('constant.roles.trust_admin'),
                config('constant.roles.hospital_admin'),
                config('constant.roles.chair'),
            ];

            if(in_array($request->time_slot,$timeSlots)){
                $timeSlots = [$request->time_slot];
            }

            $hospitalId = $request->hospital;
            $weekDays   = $request->week_days;

            //Week days
            $days_of_week = [];
            foreach ($weekDays as $key => $date) {
                $carbonDate = Carbon::parse($date);
                $formattedDate = $carbonDate->format('D, j M');
                $days_of_week[$key]['date'] = $formattedDate;

                if (in_array($authUser->primary_role, $adminRoles)) {
                    $days_of_week[$key]['statistics']['overall'] = calculateRotaTableStatistics($hospitalId,$date);
                    $days_of_week[$key]['statistics']['speciality'] = calculateRotaTableStatistics($hospitalId,$date, config('constant.roles.speciality_lead'));
                    $days_of_week[$key]['statistics']['anesthetic'] = calculateRotaTableStatistics($hospitalId,$date, config('constant.roles.anesthetic_lead'));
                    $days_of_week[$key]['statistics']['staff'] = calculateRotaTableStatistics($hospitalId,$date, config('constant.roles.staff_coordinator'));
                }
            }
            //End Week days

            $model = Hospital::select('id', 'hospital_name')->where('id', $hospitalId)->first();

            $model->days_of_week = $days_of_week;


            $model->all_rooms = $model->rooms()->select('id as value', 'room_name as label')->get();
            $model->all_status = [
                ['value' => config('constant.session_status.at_risk'), 'label' => 'At Risk'],
                ['value' => config('constant.session_status.closed'),  'label' => 'Closed'],
            ];

            //Start Rooms
            $rooms = $model->rooms()->select('id','room_name');

            //Start Apply filters
            if ($request->filter_by) {
                if ($request->filter_by == 'rooms' && $request->filter_value) {
                    $rooms = $rooms->whereIn('id', $request->filter_value);
                }
            }
            //End Apply filters

            $rooms = $rooms->get();

            $model->rooms = $rooms;
            //end Rooms

            foreach ($rooms as $room) {

                $room_records = [];

                $rolesId = [
                    config('constant.roles.speciality_lead'),
                    config('constant.roles.staff_coordinator'),
                    config('constant.roles.anesthetic_lead'),
                ];

                foreach ($timeSlots as $timeSlot) {
                    foreach ($weekDays as $key => $date) {

                        if($authUser->is_speciality_lead){

                            $specialities = $authUser->specialityDetail()->pluck('id')->toArray();

                            $record = RotaSession:: with(['users'=>function ($query) use ($authUser) {
                                $query->select('users.id', 'users.full_name')
                                    ->withPivot('status', 'role_id')
                                    ->wherePivot('role_id', $authUser->primary_role);

                            }])->select('id', 'hospital_id','speciality_id', 'time_slot','status')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot);


                            $backupSpecialityUser = BackupSpeciality::whereHas('user',function($query){
                                $query->where('primary_role',config('constant.roles.speciality_lead'));
                            })->where('user_id',$authUser->id)->where('hospital_id',$hospitalId)->first();
                
                            if($backupSpecialityUser){
                                $rotaSessionIds = $backupSpecialityUser->user->rotaSessions()->pluck('id')->toArray();
                                $record = $record->where(function($query) use($specialities,$rotaSessionIds){
                                    $query->whereIn('speciality_id',$specialities)->orWhereIn('id',$rotaSessionIds);
                                });
                            }else{
                                $record = $record->whereIn('speciality_id',$specialities);
                            }

                        }else if($authUser->is_booker){

                            $record = RotaSession::whereHas('users' ,function ($query) use ($rolesId) {

                                $query->select('users.id', 'users.full_name')
                                    ->where('rota_session_users.status', 1)
                                    ->whereIn('rota_session_users.role_id', $rolesId);

                            })->select('id', 'hospital_id','speciality_id', 'time_slot','status')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot)
                                ->where('speciality_id','!=',config('constant.unavailable_speciality_id'));

                        }else{

                            $record = RotaSession::with(['users'=>function ($query) use ($rolesId) {
                                $query->select('users.id', 'users.full_name')
                                    ->withPivot('status', 'role_id')
                                    ->wherePivotIn('role_id', $rolesId);

                            }])->select('id', 'uuid', 'quarter_id', 'hospital_id', 'speciality_id', 'time_slot','status')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot);


                            if(in_array($authUser->primary_role,$rolesId)){

                                $record = $record->where('speciality_id','!=',config('constant.unavailable_speciality_id'));

                            }

                        }


                        //Start Apply filters
                        if ($request->filter_by) {

                            if ($request->filter_by == 'speciality' && $request->filter_value) {

                                $record = $record->whereIn('speciality_id', $request->filter_value);

                            }

                            if ($request->filter_by == 'status' && $request->filter_value) {

                                $filterValue = $request->filter_value;

                                if (!is_array($filterValue)) {
                                    $filterValue = [$filterValue];
                                }
                            
                        
                                if (in_array(config('constant.session_status.closed'), $filterValue)) {
                                    $filterValue[] = config('constant.session_status.failed');
                                    $filterValue = array_unique($filterValue);
                                }
                            
                                $request->merge(['filter_value' => $filterValue]);
                            
                                $record = $record->whereIn('status', $request->filter_value);

                            }
                        }
                        //End Apply filters

                        $record = $record->first();

                        // Initialize role statuses
                        $rolesStatus = [
                            'speciality_lead' => '',
                            'anesthetic_lead' => '',
                            'staff_coordinator' => '',
                        ];

                        if ($authUser->is_speciality_lead || $authUser->is_anesthetic_lead || $authUser->is_staff_coordinator) {
                            $rolesStatus = [
                                'is_available' => '',
                            ];
                        }

                        // Group users by their role and check status
                        if ($record && $record->users) {
                            $groupedUsers = [
                                'speciality_lead' => [],
                                'anesthetic_lead' => [],
                                'staff_coordinator' => [],
                            ];

                            foreach ($record->users as $user) {
                                switch ($user->pivot->role_id) {
                                    case config('constant.roles.speciality_lead'):
                                        $groupedUsers['speciality_lead'][] = $user;
                                        break;
                                    case config('constant.roles.anesthetic_lead'):
                                        $groupedUsers['anesthetic_lead'][] = $user;
                                        break;
                                    case config('constant.roles.staff_coordinator'):
                                        $groupedUsers['staff_coordinator'][] = $user;
                                        break;
                                }
                            }

                            // Now check each group for status and set roles status
                            foreach ($groupedUsers as $role => $users) {
                                foreach ($users as $user) {
                                    $status = $user->pivot->status;

                                    if ($authUser->is_speciality_lead || $authUser->is_anesthetic_lead || $authUser->is_staff_coordinator) {
                                        if ($authUser->primary_role == $user->pivot->role_id) {
                                            $rolesStatus['is_available'] = ($status == 1);
                                        }
                                    } else {
                                        $rolesStatus[$role] = ($status == 1);
                                    }

                                }
                            }
                        }

                        $carbonDate = Carbon::parse($date);
                        $formattedDate = $carbonDate->format('D, j M');

                        $room_records[$timeSlot][$key]['date'] = $formattedDate;
                        $room_records[$timeSlot][$key]['is_disabled'] = (isset($date) && Carbon::parse($date)->gt(Carbon::now())) ? false : true;
                        $room_records[$timeSlot][$key]['rota_session_id'] = $record ? $record->id : null;

                        $room_records[$timeSlot][$key]['rota_session_status'] = $this->rotaSessionStatus($record) ? true : false;

                        $room_records[$timeSlot][$key]['speciality_id']   = $record ? $record->speciality_id : null;
                        $room_records[$timeSlot][$key]['speciality_name'] = $record ? $record->specialityDetail ? $record->specialityDetail->speciality_name : null : null;
                        $room_records[$timeSlot][$key]['roles_status']    = $rolesStatus;
                    }
                }

                $room->room_records = $room_records;
            }

            $model->is_disabled = (isset($weekDays[0]) && Carbon::parse($weekDays[0])->gt(Carbon::now())) ? false : true;

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $model,
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            // dd($e->getMessage() . '->' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message') . $e->getMessage() . '->' . $e->getLine());
        }
    }

    /**
     * Get rota table details
     */
    public function getDetails(Request $request)
    {
        $validatedData = $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
        ]);

        try {
            $timeSlots = config('constant.time_slots');

            $hospitalId = $request->hospital;
            $weekDays   = $request->week_days;

            $days_of_week = [];
            foreach ($weekDays as $date) {
                $carbonDate = Carbon::parse($date);
                $days_of_week[] = $carbonDate->format('l');
            }

            $hospitalData = Hospital::select('id', 'hospital_name')->where('id', $hospitalId)->first();

            $hospitalData->rooms = $hospitalData->rooms()->select('id', 'room_name')->get();
            $hospitalData->days_of_week = $days_of_week;


            foreach ($hospitalData->rooms as $room) {

                $room_records = [];

                foreach ($timeSlots as $timeSlot) {

                    foreach ($weekDays as $key => $date) {

                        $record = RotaSession::select('id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                            ->whereDate('week_day_date', $date)
                            ->where('time_slot', $timeSlot)
                            ->first();

                        $room_records[$timeSlot][$key]['date'] = $date;
                        $room_records[$timeSlot][$key]['value'] = ($record && (!is_null($record->speciality_id))) ? $record->speciality_id : '';

                        //Start Quarters Functionality
                        $carbonDate = Carbon::parse($date);
                        $dayOfWeek = $carbonDate->format('l'); // 'Monday', 'Tuesday', etc.
                        $currentQuarter = determineQuarter($carbonDate);

                        $quarterYear = $carbonDate->year;

                        $lastQuarterRecord = RotaSessionQuarter::select('speciality_id')
                                    ->where('quarter_no', $currentQuarter)
                                    ->where('quarter_year', $quarterYear)
                                    ->where('hospital_id', $hospitalId)
                                    ->where('room_id', $room->id)
                                    ->where('time_slot', $timeSlot)
                                    ->where('day_name', $dayOfWeek)
                                    ->first();

                        if( (!$record) && $lastQuarterRecord){
                            $room_records[$timeSlot][$key]['value'] = $lastQuarterRecord->speciality_id ?? '';
                        }
                        //End Quarters Functionality

                    }
                }

                $room->room_records = $room_records;
            }

            //Last Updated At
            $lastUpdatedAt = RotaSession::whereIn('week_day_date', $weekDays)->orderBy('updated_at','desc')->value('updated_at');
            $hospitalData->last_updated_at = Carbon::parse($lastUpdatedAt)->format('h:i A D, j M Y');

            //Selected Quater
            $quarterId = RotaSession::whereIn('week_day_date', $weekDays)
            ->groupBy('quarter_id')
            ->havingRaw('COUNT(DISTINCT week_day_date) = ?', [count($weekDays)])
            ->pluck('quarter_id')
            ->first();

            $hospitalData->quarter_id = $quarterId;

            $hospitalData->is_disabled = (isset($weekDays[0]) && Carbon::parse($weekDays[0])->gt(Carbon::now())) ? false : true;


            //Start Years Dropdown
            $date = Carbon::now();
            $years = [
                ['value' => '', 'label' => 'Select Year']
            ];

            for ($i = 0; $i < 4; $i++) {
                $year = $date->copy()->addYears($i)->year;
                $years[] = [
                    'value' => (string)$year,
                    'label' => (string)$year
                ];
            }
            $hospitalData->years = $years;
            //End Years Dropdown

            //Quarters dropdown according to current year
            $currentYear = $date->year;
            $currentQuarter = $date->quarter;

            $quarters = [];
            for ($i = 1; $i <= 4; $i++) {
                $quarters[] = [
                    'value' => "{$i}",
                    'label' => "Quarter {$i}",
                    'isDisabled' => !($i >= $currentQuarter) // Enable current and upcoming quarters, disable past quarters
                ];
            }

            array_unshift($quarters, [
                'value' => '',
                'label' => 'Apply to a Quarter',
                'isDisabled' => false
            ]);

            $hospitalData->current_quarters = $quarters;

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $hospitalData,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // dd($e->getMessage() . '->' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message') . $e->getMessage() . '->' . $e->getLine());
        }
    }


    public function saveRota(SaveRotaRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $authUser = auth()->user();

            $hospital_id = $validatedData['hospital_id'];

            // Rooms
            foreach ($validatedData['rooms'] as $room) {
                $roomId = $room['id'];
                if(isset($room['room_records'])){

                    foreach ($room['room_records'] as $date => $timeSlots) {
                        foreach ($timeSlots as $slotKey => $speciality) {

                            $isSpecialityChanged = false;
                            $speciality_name_before_changed = null;

                            $isNewCreated = false;
                            $isQuarterSet = false;

                            $start = Carbon::parse($date);
                            $dayOfWeek = $start->format('l');
                            $weekNumber = $start->weekOfYear;

                            $isCurrentQuarter = false;
                            if(isset($validatedData['quarter_id']) && isset($validatedData['quarter_year'])){

                                if($validatedData['quarter_id'] && $validatedData['quarter_year']){
                                    $isQuarterSet = true;
                                    $isCurrentQuarter = $this->isCurrentQuarter($validatedData);
                                }

                            }

                            if($isCurrentQuarter || (!$isQuarterSet)){

                                // Check if the rota session already exists
                                $rotaSession = RotaSession::where('hospital_id',$hospital_id)
                                    ->where('room_id', $roomId)
                                    ->where('time_slot', $slotKey)
                                    ->where('week_day_date', $date)
                                    ->first();

                                $rotaSessionRecords = [
                                    'quarter_id'      => $validatedData['quarter_id'] ?? null,
                                    'hospital_id'     => $hospital_id,
                                    'week_no'         => $weekNumber,
                                    'room_id'         => $roomId,
                                    'time_slot'       => $slotKey,
                                    'speciality_id'   => $speciality ?? config('constant.unavailable_speciality_id'),
                                    'week_day_date'   => $date,
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
                                }

                            }

                            //Store & Update records for manage quarters functionality
                            if($isQuarterSet){

                                $quarterNo   = $validatedData['quarter_id'];
                                $quarterYear = $validatedData['quarter_year'];

                                $quarterWeek = RotaSessionQuarter::select('id','speciality_id')
                                ->where('quarter_no', $quarterNo)
                                ->where('quarter_year',$quarterYear)
                                ->where('hospital_id', $hospital_id)
                                ->where('room_id', $roomId)
                                ->where('time_slot', $slotKey)
                                ->where('day_name', $dayOfWeek)
                                ->first();

                                $quarterRecords = [
                                    'quarter_no'      => $quarterNo,
                                    'quarter_year'    => $quarterYear,
                                    'hospital_id'     => $hospital_id,
                                    'room_id'         => $roomId,
                                    'time_slot'       => $slotKey,
                                    'day_name'        => $dayOfWeek,
                                    'speciality_id'   => $speciality ?? config('constant.unavailable_speciality_id'),
                                ];

                                if($quarterWeek){
                                    // Update existing quarter records
                                    RotaSessionQuarter::where('id',$quarterWeek->id)->update($quarterRecords);
                                }else{
                                    RotaSessionQuarter::create($quarterRecords);
                                }
                                  
                            }
                            //End Store & Update records for manage quarters functionality

                            // Start Availability Users
                           $rolesId = [
                                config('constant.roles.speciality_lead'),
                                config('constant.roles.staff_coordinator'),
                                config('constant.roles.anesthetic_lead'),
                            ];

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
                                         'created_by'        => $authUser->id
                                     ];

                                    $user->notify(new SendNotification($messageData));
                                }

                                $rotaSession->users()->sync([]);
                            }


                            if((!$isQuarterSet) && isset($rotaSession)){
                                if($rotaSession->speciality_id != config('constant.unavailable_speciality_id')){

                                    //Send notification for session confirmation to speciality lead user
                                    $specialityUsers = $rotaSession->specialityDetail ? $rotaSession->specialityDetail->users()->where('primary_role', config('constant.roles.speciality_lead'))->whereHas('getHospitals', function ($query) use($hospital_id) {
                                        $query->where('hospital_id', $hospital_id);
                                    })->get() : [];

                                    foreach ($specialityUsers as $user) {

                                        if($isNewCreated || $isSpecialityChanged){

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
                                                'created_by'        => $authUser->id
                                            ];

                                            $user->notify(new SendNotification($messageData));
                                        }
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

                                        if($isNewCreated || $isSpecialityChanged){

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
                                                'created_by'        => $authUser->id
                                            ];

                                            $user->notify(new SendNotification($messageData));
                                        }
                                    }
                                    //End send notification for session confirmation to anesthetic lead & staff coordinator
                                }
                            }


                        }


                    }
                }

            }
            // End Rooms

            // Check if remaining days need to be processed
            if ($isQuarterSet) {
                $quarterNo   = $validatedData['quarter_id'];
                $quarterYear = $validatedData['quarter_year'];
                $currentYear = Carbon::now()->year;

                $quaterDates =  getQuarterDates($quarterNo, $quarterYear);
                $startOfQuarter = $quaterDates[0] ?? null;
                $endOfQuarter   = $quaterDates[1] ?? null;

                if($startOfQuarter && $endOfQuarter){

                    $lastProcessedDate =  $startOfQuarter;

                    if( ($currentYear == $quarterYear) && isset($validatedData['week_days'][6]) ){
                        $dateExistsInQuarter = isDateInQuarter($validatedData['week_days'][6], $startOfQuarter, $endOfQuarter);
                        if($dateExistsInQuarter){
                            $lastProcessedDate =  Carbon::parse($validatedData['week_days'][6])->copy()->addDay();
                        }
                    }

                    // Get the remaining days in the quarter
                    $remainingDays = $this->getRemainingQuarterDays($startOfQuarter, $endOfQuarter, $lastProcessedDate);

                    // Dispatch the job to handle remaining days
                    if ($remainingDays->count() > 0) {
                        SetQuarterDays::dispatch([
                            'quarter_id'    => $quarterNo,
                            'quarter_year'  => $quarterYear,
                            'hospital_id'   => $hospital_id,
                            'remaining_days'=> $remainingDays,
                            'created_by'    => $authUser->id
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->respondOk([
                'status'  => true,
                'message' => trans('messages.record_saved_successfully'),
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)
                ->respondWithError(trans('messages.error_message') . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    private function getRemainingQuarterDays($startOfQuarter, $endOfQuarter, $lastProcessedDate)
    {
        $remainingDays = collect();

        if($startOfQuarter && $endOfQuarter){

            // Ensure the start date for remaining days is the day after the last processed date
            $startFrom = $lastProcessedDate;

            // Generate a period from start date to end of quarter
            $period = CarbonPeriod::create($startFrom, $endOfQuarter);

            // Collect the dates from the period
            $remainingDays = collect($period->toArray())->map(function ($date) {
                return $date->format('Y-m-d');
            });

        }

        return $remainingDays;

    }


    public function updateAvailability(UpdateAvailablityRequest $request)
    {
        $authUser = auth()->user();

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            foreach ($validatedData['rota_sessions'] as $sessions) {

                foreach ($sessions as $id => $is_available) {

                    $rota_session = RotaSession::where('id', $id)->where('speciality_id', '!=', null)->first();
                    if ($rota_session) {

                        if(in_array($is_available, array(true,false) ) ){

                            $is_available = $is_available ? 1 : 2;

                            $existingRecord = $rota_session->users()
                                ->wherePivot('role_id', $authUser->primary_role)
                                ->first();

                            if ($existingRecord) {
                                // Update the existing record's status
                                $rota_session->users()
                                ->newPivotStatement()
                                ->where('rota_session_id', $rota_session->id)
                                ->where('role_id', $authUser->primary_role)
                                ->update([
                                    'user_id' => $authUser->id,
                                    'status' => $is_available,
                                ]);

                            } else {
                                // Sync the new availability data
                                $availability_user[$authUser->id] = ['role_id' => $authUser->primary_role,'status' => $is_available];
                                $rota_session->users()->attach($authUser->id, $availability_user[$authUser->id]);
                            }

                            // All user confirm their availablity than notify booker
                            $rolesId = [
                                config('constant.roles.speciality_lead'),
                                config('constant.roles.staff_coordinator'),
                                config('constant.roles.anesthetic_lead'),
                            ];

                            $allRolesConfirmed = DB::table('rota_session_users')
                            ->where('rota_session_id', $rota_session->id)
                            ->whereIn('role_id', $rolesId)
                            ->where('status', 1)
                            ->distinct()
                            ->count('role_id') === count($rolesId);

                            if($allRolesConfirmed) {

                                $bookerUsers = $rota_session->hospitalDetail->users()->where('primary_role',config('constant.roles.booker'))->get();

                                foreach($bookerUsers as $user){

                                    $subject = trans('messages.notify_subject.confirmed_booking');
                                    $notification_type = array_search(config('constant.notification_type.session_confirmed'), config('constant.notification_type'));
    
                                    $messageContent = $rota_session->hospitalDetail->hospital_name.' - '.$rota_session->roomDetail->room_name;

                                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));
    
                                    $messageData = [
                                        'notification_type' => $notification_type,
                                        'section'           => $key,
                                        'subject'           => $subject,
                                        'message'           => $messageContent,
                                        'rota_session'      => $rota_session,
                                        'created_by'        => $authUser->id
                                    ];
    
                                    $user->notify(new SendNotification($messageData));

                                }

                            }
                           //End  all user confirm their availablity notify booker


                            //Notify to admin users (System Admin, Trust Admins, Hospital Admins)
                             /*   $adminUsers = $rota_session->hospitalDetail->users()->whereIn('primary_role',[config('constant.roles.trust_admin'),config('constant.roles.hospital_admin')])->select('id','full_name','user_email')->get();

                                $superAdmin = User::where('primary_role', config('constant.roles.system_admin'))->select('id', 'full_name', 'user_email')->first();
                                if ($superAdmin) {
                                    $adminUsers = $adminUsers->concat([$superAdmin]);
                                }

                                if($adminUsers){

                                    foreach($adminUsers as $user){

                                       $roleName = $authUser->role->role_name;

                                       $subject = trans('messages.notification_subject.confirm',['roleName'=>$roleName]);
                                       $notification_type = array_search(config('constant.notification_type.session_confirmed'), config('constant.notification_type'));

                                       if($is_available == 2){
                                            $subject = trans('messages.notification_subject.cancel',['roleName'=>$roleName]);

                                            $notification_type = array_search(config('constant.notification_type.session_cancelled'), config('constant.notification_type'));
                                       }


                                       $messageContent = $rota_session->roomDetail->room_name.' - '. $rota_session->specialityDetail->speciality_name;

                                       $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                                       $messageData = [
                                            'notification_type' => $notification_type,
                                            'section'           => $key,
                                            'subject'           => $subject,
                                            'message'           => $messageContent,
                                            'rota_session'      => $rota_session,
                                            'created_by'        => $authUser->id
                                        ];

                                        $user->notify(new SendNotification($messageData));

                                    }

                                }
                            */
                            // End to Notify to admin users

                        }

                    }


                }
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_updated_successfully')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)
                ->respondWithError(trans('messages.error_message') . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }


    public function rotaTableDropdown(Request $request){

        $validatedData = $request->validate([
            'speciality_type'=> ['nullable'],
        ]);

        $responseData = [];

        // Retrieve quarters
        $quarters = Quarter::select('id as value', 'quarter_name as label')->get();

        $quarters->prepend([
            'value' => '',
            'label' => 'Apply to a Quarter'
        ]);

        $responseData['quarters'] = $quarters;

        // Retrieve specialities
        $specialities = Speciality::pluck('speciality_name','id');
        $responseData['specialities'] = collect($specialities);

        // Retrieve hospitals
        $hospitals = [];
        $responseData['hospitals'] = Hospital::pluck('hospital_name','id');

        if(!auth()->user()->is_system_admin){
            $responseData['hospitals'] = auth()->user()->getHospitals()->pluck('hospital_name','id');
        }


        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $responseData,
        ])->setStatusCode(Response::HTTP_OK);

    }

    public function rotaTableFitlerDropdown(Request $request){
        $validatedData = $request->validate([
            'hospital'=> ['required']
        ]);

        $responseData = [];

        //Retrieve Rooms
        $responseData['all_rooms'] = Room::select('id as value', 'room_name as label')->where('hospital_id',$request->hospital)->get();

        // Retrieve specialities
        $responseData['specialities'] = Speciality::select('id as value','speciality_name as label')->where('id','!=',10)->get();

        //Retrieve Status
        $responseData['all_status'] = [
                ['value' => 1, 'label' => 'Closed'],
                ['value' => 2, 'label' => 'At Risk']
        ];

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $responseData,
        ])->setStatusCode(Response::HTTP_OK);

    }


    private function rotaSessionStatus($session){

        if($session){

            if($session->status == 2){

                $backupSpeciality = BackupSpeciality::whereHas('user',function($query){
                    $query->where('primary_role',config('constant.roles.speciality_lead'));
                })->where('user_id',auth()->user()->id)->where('hospital_id',$session->hospital_id)->first();

                if($backupSpeciality){

                    $existingRecord = $session->users()
                    ->wherePivot('role_id', config('constant.roles.speciality_lead'))
                    ->wherePivot('user_id', $backupSpeciality->user_id)->first();

                    if( $existingRecord ){
                        return false;
                    }

                }

                return true;

            }elseif($session->status == 3){

                return true;
            }

        }

        return false;

    }


    private function isCurrentQuarter($validatedData){
        $quarterNo   = $validatedData['quarter_id'];
        $quarterYear = $validatedData['quarter_year'];
        $currentYear = Carbon::now()->year;

        $quaterDates =  getQuarterDates($quarterNo, $quarterYear);
        $startOfQuarter = $quaterDates[0] ?? null;
        $endOfQuarter   = $quaterDates[1] ?? null;

        if($startOfQuarter && $endOfQuarter){

            $lastProcessedDate =  $startOfQuarter;

            if( ($currentYear == $quarterYear) && isset($validatedData['week_days'][6]) ){
                $dateExistsInQuarter = isDateInQuarter($validatedData['week_days'][6], $startOfQuarter, $endOfQuarter);
                if($dateExistsInQuarter){
                    return true;
                }
            }
        }

        return false;
            
    }
}
