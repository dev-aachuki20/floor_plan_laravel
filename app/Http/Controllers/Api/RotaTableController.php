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
use App\Mail\AvailablityStatusMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RotaTable\SaveRotaRequest;
use App\Http\Requests\RotaTable\UpdateAvailablityRequest;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


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
            ];

            if(in_array($request->time_slot,$timeSlots)){
                $timeSlots = [$request->time_slot];
            }

            $hospitalId = $request->hospital;
            $weekDays   = $request->week_days;

            $startDate = $validatedData['week_days'][0];
            $endDate = $validatedData['week_days'][6];

            $start = Carbon::parse($startDate);
            $weekNumber = $start->weekOfYear;

            //Week days
            $days_of_week = [];
            foreach ($weekDays as $key => $date) {
                $carbonDate = Carbon::parse($date);
                $formattedDate = $carbonDate->format('D, j M');
                $days_of_week[$key]['date'] = $formattedDate;

                if (in_array($authUser->primary_role, $adminRoles)) {
                    $days_of_week[$key]['statistics']['overall'] = 0;
                    $days_of_week[$key]['statistics']['speciality'] = 0;
                    $days_of_week[$key]['statistics']['anesthetic'] = 0;
                    $days_of_week[$key]['statistics']['staff'] = 0;
                }
            }
            //End Week days

            $model = Hospital::select('id', 'hospital_name')->where('id', $hospitalId)->first();
            $model->days_of_week = $days_of_week;
            $model->rota_table = $model->rotaTable()->select('id', 'uuid', 'quarter_id', 'hospital_id', 'week_no', 'week_start_date', 'week_end_date')->where('week_no', $weekNumber)->where(function ($query) use ($weekDays) {
                $query->whereDate('week_start_date', '<=', min($weekDays))
                    ->whereDate('week_end_date', '>=', max($weekDays));
            })->first();

            $model->all_rooms = $model->rooms()->select('id as value', 'room_name as label')->get();
            $model->all_status = [
                ['value' => 1, 'label' => 'Closed'],
                ['value' => 2, 'label' => 'At Risk']
            ];

            //Start Apply filters
            if ($request->filter_by) {
                if ($request->filter_by == 'rooms' && $request->filter_value) {
                    $model->rooms = $model->rooms()->whereIn('id', $request->filter_value)->get();
                }
            }
            //End Apply filters


            foreach ($model->rooms as $room) {

                $room_records = [];

                foreach ($timeSlots as $timeSlot) {
                    foreach ($weekDays as $key => $date) {

                        $record = RotaSession::with(['users' => function ($query) use ($authUser) {
                            // $query->select('users.id', 'users.full_name') 
                            //       ->addSelect('rota_session_users.status');

                            $rolesId = [
                                config('constant.roles.speciality_lead'),
                                config('constant.roles.staff_coordinator'),
                                config('constant.roles.anesthetic_lead'),
                            ];

                            $query->select('users.id', 'users.full_name')
                                ->withPivot('status', 'role_id')
                                ->wherePivotIn('role_id', $rolesId);

                            if (in_array($authUser->primary_role, $rolesId)) {
                                $query->where('users.id', $authUser->id);
                            }
                        }])->select('id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                            ->whereDate('week_day_date', $date)
                            ->where('time_slot', $timeSlot);

                        //Start Apply filters
                        if ($request->filter_by) {

                            if ($request->filter_by == 'specialty' && $request->filter_value) {

                                $record = $record->whereIn('speciality_id', $request->filter_value);
                            }
                        }
                        //End Apply filters

                        $record = $record->first();

                        // Calculate statistics
                        if ($record) {
                            $statistics = calculateRoleStatistics($record->users ?? collect());

                            // Update day statistics
                            $days_of_week[$key]['statistics']['overall'] += $statistics['overall'];
                            $days_of_week[$key]['statistics']['speciality'] += $statistics['speciality_lead'];
                            $days_of_week[$key]['statistics']['anesthetic'] += $statistics['anesthetic_lead'];
                            $days_of_week[$key]['statistics']['staff'] += $statistics['staff_coordinator'];
                        }

                        // Initialize role statuses
                        $rolesStatus = [
                            'speciality_lead' => false,
                            'anesthetic_lead' => false,
                            'staff_coordinator' => false,
                        ];

                        if ($authUser->is_speciality_lead) {
                            $rolesStatus = [
                                'speciality_lead' => false,
                            ];
                        } else if ($authUser->is_anesthetic_lead) {
                            $rolesStatus = [
                                'anesthetic_lead' => false,
                            ];
                        } else if ($authUser->is_staff_coordinator) {
                            $rolesStatus = [
                                'staff_coordinator' => false,
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
                                        $rolesStatus[$role] = $user->pivot->status == 1 ? true : false;

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
                        $room_records[$timeSlot][$key]['users'] = $record ? $record->users : null;
                        $room_records[$timeSlot][$key]['roles_status'] = $rolesStatus;
                    }
                }

                // Assign the records to the room
                $room->room_records = $room_records;
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $model,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            dd($e->getMessage() . '->' . $e->getLine());
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

            $startDate = $validatedData['week_days'][0];
            $endDate = $validatedData['week_days'][6];

            $start = Carbon::parse($startDate);
            $weekNumber = $start->weekOfYear;


            $days_of_week = [];
            foreach ($weekDays as $date) {
                $carbonDate = Carbon::parse($date);
                $days_of_week[] = $carbonDate->format('l');
            }

            $hospitalData = Hospital::select('id', 'hospital_name')->where('id', $hospitalId)->first();

            $hospitalData->rota_table = $hospitalData->rotaTable()->select('id', 'uuid', 'quarter_id', 'hospital_id', 'week_no', 'week_start_date', 'week_end_date')->where('week_no', $weekNumber)->where('hospital_id', $hospitalId)->where(function ($query) use ($weekDays) {
                $query->whereDate('week_start_date', '<=', min($weekDays))
                    ->whereDate('week_end_date', '>=', max($weekDays));
            })->first();

            $hospitalData->rooms = $hospitalData->rooms()->select('id', 'room_name')->get();
            $hospitalData->days_of_week = $days_of_week;


            foreach ($hospitalData->rooms as $room) {

                $room_records = [];

                foreach ($timeSlots as $timeSlot) {

                    foreach ($weekDays as $key => $date) {

                        $record = RotaSession::with(['users' => function ($query) {
                            $query->select('users.id', 'users.full_name')
                                ->addSelect('rota_session_users.status');
                        }])->select('id', 'speciality_id', 'time_slot')->where('room_id', $room->id)
                            ->whereDate('week_day_date', $date)
                            ->where('time_slot', $timeSlot)
                            ->first();

                        $room_records[$timeSlot][$key]['date'] = $date;
                        $room_records[$timeSlot][$key]['value'] = $record ? $record->speciality_id : null;
                    }
                }

                // Assign the records to the room
                $room->room_records = $room_records;
            }


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

            $startDate = $validatedData['week_days'][0];
            $endDate = $validatedData['week_days'][6];

            $start = Carbon::parse($startDate);
            $weekNumber = $start->weekOfYear;

            $rotaRecords = [
                'quarter_id'      => $validatedData['quarter_id'],
                'hospital_id'     => $validatedData['hospital_id'],
                'week_no'         => $weekNumber,
                'week_start_date' => $startDate,
                'week_end_date'   => $endDate,
            ];

            if ($uuid) {
                $existingRota = Rota::where('uuid', $uuid)->first();
                $existingRota->update($rotaRecords);
                $rota = $existingRota;
            } else {
                $rota = Rota::create($rotaRecords);
            }

            // Rooms
            foreach ($validatedData['rooms'] as $room) {
                $roomId = $room['id'];
                foreach ($room['room_records'] as $date => $timeSlots) {
                    foreach ($timeSlots as $slotKey => $speciality) {
                        // Check if the rota session already exists
                        $rotaSession = $rota->rotaSession()
                            ->where('room_id', $roomId)
                            ->where('time_slot', $slotKey)
                            ->where('week_day_date', $date)
                            ->first();

                        $rotaSessionRecords = [
                            'room_id'       => $roomId,
                            'time_slot'     => $slotKey,
                            'speciality_id' => $speciality,
                            'week_day_date' => $date,
                        ];

                        if ($rotaSession) {
                            // Update existing rota session
                            $rotaSession->update($rotaSessionRecords);
                        } else {
                            // Create new rota session
                            $rotaSession = $rota->rotaSession()->create($rotaSessionRecords);
                        }

                        // Start Availability Users
                        $rolesId = [
                            config('constant.roles.speciality_lead'),
                            config('constant.roles.staff_coordinator'),
                            config('constant.roles.anesthetic_lead'),
                        ];

                        $availabilityUsers = $rota->hospitalDetail->users()
                            ->whereIn('primary_role', $rolesId)
                            ->whereHas('specialityDetail', function ($query) use ($speciality) {
                                $query->where('speciality_id', $speciality);
                            })
                            ->with('specialityDetail')
                            ->get();

                        $availability_user = [];
                        foreach ($availabilityUsers as $user) {
                            $availability_user[$user->id] = ['role_id' => $user->primary_role, 'status' => 0];
                        }

                        if (count($availability_user) > 0) {
                            $rotaSession->users()->sync($availability_user);

                            foreach ($rotaSession->users as $user) {
                                $subject = "Upcoming Session";
                                Mail::to($user->user_email)->queue(new RotaSessionMail($subject, $user, $rotaSession));
                            }
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


    public function updateAvailability(UpdateAvailablityRequest $request, $uuid)
    {
        $authUser = auth()->user();

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $rota = Rota::where('uuid', $uuid)->first();
            if ($rota && $validatedData['rota_sessions']) {

                foreach ($validatedData['rota_sessions'] as $sessions) {

                    foreach ($sessions as $id => $is_available) {

                        $rota_session = $rota->rotaSession()->where('id', $id)->where('speciality_id', '!=', null)->first();
                        if ($rota_session) {

                            $availability_data = ['role_id' => $authUser->primary_role, 'status' => 0];
                            $rota_session->users()->updateExistingPivot($authUser->id, $availability_data);


                            $existingConfirmed = $rota_session->users()->wherePivot('status', 1)->wherePivot('role_id', $authUser->primary_role)->wherePivot('user_id', '!=', $authUser->id)->exists();

                            if ($existingConfirmed) {

                                $speciality_name = $rota_session->specialityDetail ? $rota_session->specialityDetail->speciality_name : '';
                                return $this->setStatusCode(403)
                                ->respondWithError(trans('messages.already_confirm_session',['sessionName'=>$speciality_name,'sessionDate'=>$rota_session->week_day_date]));

                                return response()->json(['message' => 'This session has already been confirmed by another user.'], 403);
                            } else {

                                // $availability_data = ['role_id' => $authUser->primary_role, 'status' => $is_available];
                                $availability_data = ['role_id' => $authUser->primary_role, 'status' => 1];

                                $rota_session->users()->updateExistingPivot($authUser->id, $availability_data);

                                // Detach all other users from the rota session except the current user
                                //$rota_session->users()->wherePivot('user_id', '!=', $authUser->id)->wherePivot('role_id', $authUser->primary_role)->detach();

                               
                                //Notify to admin users (System Admin, Trust Admins, Hospital Admins)
                                    $adminUsers = $rota->hospitalDetail->users()->whereIn('primary_role',[config('constant.roles.trust_admin'),config('constant.roles.hospital_admin')])->select('id','full_name','user_email')->get();
                                
                                    $superAdmin = User::where('primary_role', config('constant.roles.system_admin'))->select('id', 'full_name', 'user_email')->first();
                                    if ($superAdmin) {
                                        $adminUsers = $adminUsers->concat([$superAdmin]);
                                    }

                                    if($adminUsers){

                                        foreach($adminUsers as $user){
                                            $roleName = $authUser->role->role_name;
                                            $subject = trans('messages.availablity_status.confirm',['roleName'=>$roleName]);
            
                                            Mail::to($user->user_email)->queue(new AvailablityStatusMail($subject, $user, $rota_session, $authUser));
                                        }

                                    }
                                // End to Notify to admin users
                                
                            }

                        }
                    }
                }
            } else {

                return $this->setStatusCode(404)
                    ->respondWithError(trans('messages.no_record_found'));
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
        $responseData['quarters'] = Quarter::select('id','quarter_name','start_date','end_date')->whereYear('start_date', $currentYear)
                        ->whereYear('end_date', $currentYear)
                        ->get();

        // Retrieve specialities 
        $specialities = Speciality::pluck('speciality_name','id');
        if($request->speciality_type == 'list'){
            $specialities[null] = 'Unavailable';
        }          
        $responseData['specialities'] = collect($specialities);
                        
        // Retrieve hospitals 
        $hospitals = [];
        $responseData['hospitals'] = Hospital::pluck('hospital_name','id');
        if(auth()->user()){

            if(auth()->user()->is_trust_admin || auth()->user()->is_hospital_admin){
                $responseData['hospitals'] = auth()->user()->getHospitals()->pluck('hospital_name','id');
            }
        } 

            
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $responseData,
        ])->setStatusCode(Response::HTTP_OK);

    }
    
}
