<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Room;
use App\Models\Rota;
use App\Models\RotaSession;
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
                ['value' => 1, 'label' => 'Closed'],
                ['value' => 2, 'label' => 'At Risk']
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
                            $record = RotaSession::whereHas('users' ,function ($query) use ($authUser) {

                                $query->select('users.id', 'users.full_name')
                                    ->where('rota_session_users.role_id', config('constant.roles.speciality_lead'))
                                    ->where('users.id', $authUser->id);

                            })->select('id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot);

                        }else if($authUser->is_booker){

                            $record = RotaSession::whereHas('users' ,function ($query) use ($authUser, $rolesId) {

                                $query->select('users.id', 'users.full_name')
                                    ->where('rota_session_users.status', 1)
                                    ->whereIn('rota_session_users.role_id', $rolesId);

                            })->select('id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot);

                        }else{

                            $record = RotaSession::with(['users'=>function ($query) use ($authUser,$rolesId) {
                                $query->select('users.id', 'users.full_name')
                                    ->withPivot('status', 'role_id')
                                    ->wherePivotIn('role_id', $rolesId);

                            }])->select('id', 'uuid', 'quarter_id', 'hospital_id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                                ->whereDate('week_day_date', $date)
                                ->where('time_slot', $timeSlot);

                        }


                        //Start Apply filters
                        if ($request->filter_by) {

                            if ($request->filter_by == 'speciality' && $request->filter_value) {

                                $record = $record->whereIn('speciality_id', $request->filter_value);

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

                            // Now check each group for status and set rolesStatus
                            foreach ($groupedUsers as $role => $users) {
                                foreach ($users as $user) {
                                    if ($user->pivot->status) {
                                        $status = $user->pivot->status;

                                        if ($authUser->is_speciality_lead || $authUser->is_anesthetic_lead || $authUser->is_staff_coordinator) {
                                            if ($authUser->id == $user->id) {

                                                if($status == 1){
                                                    $rolesStatus['is_available'] = true;
                                                }else if($status == 2){
                                                    $rolesStatus['is_available'] = false;
                                                }

                                                // $rolesStatus['is_available'] = ($user->pivot->status == 1) ? true : false;
                                            }
                                        }else{

                                            if($status == 1){
                                                $rolesStatus[$role] = true;
                                            }else if($status == 2){
                                                $rolesStatus[$role] = false;
                                            }

                                            // $rolesStatus[$role] = $user->pivot->status == 1 ? true : false;
                                        }

                                        if ($user->pivot->status == 1) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        $carbonDate = Carbon::parse($date);
                        $formattedDate = $carbonDate->format('D, j M');


                        $room_records[$timeSlot][$key]['date'] = $formattedDate;
                        $room_records[$timeSlot][$key]['rota_session_id'] = $record ? $record->id : null;
                        $room_records[$timeSlot][$key]['speciality_id']   = $record ? $record->speciality_id : null;
                        $room_records[$timeSlot][$key]['speciality_name'] = $record ? $record->specialityDetail ? $record->specialityDetail->speciality_name : 'Unavailable' : 'Unavailable';
                        // $room_records[$timeSlot][$key]['users'] = $record ? $record->users : null;
                        $room_records[$timeSlot][$key]['roles_status'] = $rolesStatus;
                    }
                }

                // Assign the records to the room
                $room->room_records = $room_records;
            }

            //Disable dates
            $model->is_disabled = (isset($weekDays[0]) && $weekDays[0] == date('Y-m-d')) ? true : false;

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

            $hospitalData->is_disabled = (isset($weekDays[0]) && $weekDays[0] == date('Y-m-d')) ? true : false;

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


    public function saveRota(SaveRotaRequest $request, $uuid = null)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $hospital_id = $validatedData['hospital_id'];

            // Rooms
            foreach ($validatedData['rooms'] as $room) {
                $roomId = $room['id'];
                foreach ($room['room_records'] as $date => $timeSlots) {
                    foreach ($timeSlots as $slotKey => $speciality) {

                        $isSpecialityChanged = false;

                        $start = Carbon::parse($date);
                        $weekNumber = $start->weekOfYear;

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
                            'speciality_id'   => $speciality ?? null,
                            'week_day_date'   => $date,
                        ];


                        if ($rotaSession) {

                            if(!is_null($rotaSession->speciality_id)){

                                if($rotaSession->speciality_id != $rotaSessionRecords['speciality_id']){
                                    $isSpecialityChanged = true;
                                }

                            }

                            // Update existing rota session
                            $rotaSession->update($rotaSessionRecords);
                        } else {
                            // Create new rota session
                            $rotaSession = RotaSession::create($rotaSessionRecords);
                        }

                        // Start Availability Users
                       $rolesId = [
                            config('constant.roles.speciality_lead'),
                            // config('constant.roles.staff_coordinator'),
                            // config('constant.roles.anesthetic_lead'),
                        ];

                        $availabilityUsers = $rotaSession->specialityDetail ? $rotaSession->specialityDetail->users()->whereIn('primary_role', $rolesId)->get() : [];

                        $availability_user = [];
                        foreach ($availabilityUsers as $user) {

                            $availability_user[$user->id] = ['role_id' => $user->primary_role];
                           
                        }

                        if (count($availability_user) > 0) {

                            if($isSpecialityChanged){
                                $rotaSession->users()->sync($availability_user);
                            }else{
                                $rotaSession->users()->syncWithoutDetaching($availability_user);
                            }
                           
                            // $rotaSession->users()->sync($availability_user);

                        }else{
                            $rotaSession->users()->sync($availability_user);
                        }
                        // End Availability Users


                    }
                }
            }
            // End Rooms

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

    public function getQuarters()
    {

        $currentYear = date('Y');

        // Retrieve quarters for the current year
        $quarters = Quarter::select('id', 'quarter_name', 'start_date', 'end_date')->whereYear('start_date', $currentYear)
            ->whereYear('end_date', $currentYear)
            ->get();

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $quarters,
        ])->setStatusCode(Response::HTTP_OK);
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

                        $existingConfirmed = $rota_session->users()->wherePivot('status', 1)->wherePivot('role_id', $authUser->primary_role)->wherePivot('user_id', '!=', $authUser->id)->exists();

                        if ($existingConfirmed) {

                            $speciality_name = $rota_session->specialityDetail ? $rota_session->specialityDetail->speciality_name : '';

                            return $this->setStatusCode(403)
                            ->respondWithError(trans('messages.already_confirm_session',['sessionName'=>$speciality_name,'sessionDate'=>$rota_session->week_day_date]));

                        } else if( in_array($is_available, array(true,false) ) ){

                            $is_available = $is_available ? 1 : 2;

                            $existingRecord = $rota_session->users()
                                ->wherePivot('user_id', $authUser->id)
                                ->wherePivot('role_id', $authUser->primary_role)
                                ->first();

                            if ($existingRecord) {
                                // Update the existing record's status
                                $rota_session->users()->updateExistingPivot($authUser->id, ['status' => $is_available]);
                            } else {
                                // Sync the new availability data
                                $availability_user[$authUser->id] = ['role_id' => $authUser->primary_role,'status' => $is_available];
                                // $rota_session->users()->sync($availability_user);
                                $rota_session->users()->attach($authUser->id, $availability_user[$authUser->id]);

                            }
                           
                            //Notify to admin users (System Admin, Trust Admins, Hospital Admins)
                                $adminUsers = $rota_session->hospitalDetail->users()->whereIn('primary_role',[config('constant.roles.trust_admin'),config('constant.roles.hospital_admin')])->select('id','full_name','user_email')->get();

                                $superAdmin = User::where('primary_role', config('constant.roles.system_admin'))->select('id', 'full_name', 'user_email')->first();
                                if ($superAdmin) {
                                    $adminUsers = $adminUsers->concat([$superAdmin]);
                                }

                                if($adminUsers){

                                    foreach($adminUsers as $user){

                                       $roleName = $authUser->role->role_name;

                                       $subject = trans('messages.availablity_status.confirm',['roleName'=>$roleName]);
                                       $notification_type = array_search(config('constant.subject_notification_type.session_confirmed'), config('constant.subject_notification_type'));

                                       if($is_available == 2){
                                            $subject = trans('messages.availablity_status.cancel',['roleName'=>$roleName]);
                                           
                                            $notification_type = array_search(config('constant.subject_notification_type.session_cancelled'), config('constant.subject_notification_type'));
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
            'speciality_type'=> ['nullable']
        ]);

        $responseData = [];

        // Retrieve quarters
        $currentYear = date('Y');
        $quarters = Quarter::select('id as value', 'quarter_name as label')
            ->whereYear('start_date', $currentYear)
            ->whereYear('end_date', $currentYear)
            ->get();
        
        $quarters->prepend([
            'value' => '',
            'label' => 'Apply to a Quarter'
        ]);
    
        $responseData['quarters'] = $quarters;

        // Retrieve specialities
        $specialities = Speciality::pluck('speciality_name','id');
        // if($request->speciality_type == 'list'){
        //     $specialities[''] = 'Unavailable';
        // }
        $responseData['specialities'] = collect($specialities);

        // Retrieve hospitals
        $hospitals = [];
        $responseData['hospitals'] = Hospital::pluck('hospital_name','id');
        if(auth()->user()){

            if(!auth()->user()->is_system_admin){
                $responseData['hospitals'] = auth()->user()->getHospitals()->pluck('hospital_name','id');
            }
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

}
