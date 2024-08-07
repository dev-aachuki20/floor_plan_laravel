<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Rota;
use App\Models\RotaSession;
use App\Models\Quarter;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Mail\RotaSessionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RotaTable\StoreRequest;
use App\Http\Requests\RotaTable\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class RotaTableController extends APIController
{
    public function index(Request $request)
    {
        $request->validate([
            'filter_value'   => 'nullable|array',
            'filter_value.*' => 'integer',
        ]);

        try {
            DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");


            // $hospital = Hospital::first();

            // dd($hospital->rooms()->get());

            $model = RotaSession::query();

            //Start Apply filters
            if ($request->filter_by) {

                if ($request->filter_by == 'rooms' && $request->filter_value) {

                    $model = $model->whereRelation('hospitalDetail.rooms', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                } else if ($request->filter_by == 'specialty' && $request->filter_value) {

                    $model = $model->whereRelation('user.specialityDetail', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                }
            }
            //End Apply filters

            $getAllRecords = $model->orderBy('created_at', 'desc')->paginate(10);

            if ($getAllRecords->count() > 0) {
                foreach ($getAllRecords as $record) {
                    // dd($record->procedure);
                    //     $record->full_name = ucwords($record->full_name);
                    //    $record->procedure_id = $record->procedure->value('id');
                    $record->procedure_name = $record->procedure->value('procedures_name');
                    //     $record->speciality =   $record->specialityDetail()->value('speciality_name');
                    //     $record->sub_speciality = $record->subSpecialityDetail()->value('sub_speciality_name');
                    //     $record->trust = $record->trusts()->pluck('trust_name', 'id')->toArray();
                    //     $record->hospitals = $record->getHospitals()->pluck('hospital_name')->toArray();
                }
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $getAllRecords,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            dd($e->getMessage() . '->' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Get rota table details
     */
    public function getDetails(Request $request)
    {
        $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer','exists:hospital,id,deleted_at,NULL'],
        ]);

        try {
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


            $timeSlots = config('constant.time_slots');

            foreach ($hospitalData->rooms as $room) {
            
                $room_records = [];

                foreach ($weekDays as $date) {
                    $room_records[$date] = [];

                    foreach ($timeSlots as $timeSlot) {
                        
                        $record = RotaSession::with(['users' => function($query) {
                            $query->select('users.id', 'users.full_name') 
                                  ->addSelect('rota_session_users.status');
                        }])->select('id','speciality_id')->where('room_id', $room->id)
                            ->whereDate('week_day_date', $date)
                            ->where('time_slot', $timeSlot) 
                            ->first();

                        $room_records[$date][$timeSlot] = $record ? $record : null;
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
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreRequest $request)
    {
        try {   
            DB::beginTransaction();
 
            $validatedData = $request->validated();

            $startDate = $validatedData['week_days'][0];
            $endDate = $validatedData['week_days'][6];

            $start = Carbon::parse($startDate);
            $weekNumber = $start->weekOfYear;

            $rotaRecords = [
                'quarter_id'            => $validatedData['quarter_id'],
                'hospital_id'           => $validatedData['hospital_id'],
                'week_no'               => $weekNumber,
                'week_start_date'       => $startDate,
                'week_end_date'         => $endDate,
            ];

            $createdRota = Rota::create($rotaRecords);

           if($createdRota){

                //Rooms
                foreach ($validatedData['rooms'] as $room) {
                    $roomId = $room['id'];
                    foreach ($room['room_records'] as $date => $timeSlots) {
                        foreach ($timeSlots as $slotKey => $speciality) {

                            //Rota Session Table
                            $rotaSessionRecords = [
                                'room_id'       => $roomId,
                                'time_slot'     => $slotKey,
                                'speciality_id' => $speciality,
                                'week_day_date' => $date,
                            ];
                            
                            $createdRotaSession = $createdRota->rotaSession()->create($rotaSessionRecords);
                          
                            //Start Availability Users
                            $rolesId = [
                                config('constant.roles.speciality_lead'),
                                config('constant.roles.staff_coordinator'),
                                config('constant.roles.anesthetic_lead'),
                            ];

                            $availabilityUsers = $createdRota->hospitalDetail->users()
                                    ->whereIn('primary_role',$rolesId)
                                    ->whereHas('specialityDetail', function ($query) use($speciality){
                                        $query->where('speciality_id', $speciality);
                                    })
                                    ->with('specialityDetail')
                                    ->get();
                            
                            $availability_user = [];
                            foreach($availabilityUsers as $user){
                                $availability_user[$user->id] = ['role_id' => $user->primary_role, 'status' => 0];
                            }

                            if(count($availability_user) > 0){
                                $createdRotaSession->users()->sync($availability_user);

                                foreach($createdRotaSession->users as $user){
                                    $subject = "Upcoming Session";
                                    Mail::to($user->user_email)->send(new RotaSessionMail($subject, $user, $createdRotaSession));
                                }
                            }
                           
                            //End Availability Users
                        }
                      
                    }
                }
                //End Rooms

            }

            DB::commit();

            return $this->respondOk([
                'status'    => true,
                'message'   => trans('messages.record_created_successfully'),
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            //dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)
                ->respondWithError(trans('messages.error_message').$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

  
    public function update(UpdateRequest $request, $uuid)
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

            $existingRota = Rota::where('uuid', $uuid)->first();
            $existingRota->update($rotaRecords);

            // Rooms
            foreach ($validatedData['rooms'] as $room) {
                $roomId = $room['id'];
                foreach ($room['room_records'] as $date => $timeSlots) {
                    foreach ($timeSlots as $slotKey => $speciality) {
                        // Check if the rota session already exists
                        $rotaSession = $existingRota->rotaSession()
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
                            $rotaSession = $existingRota->rotaSession()->create($rotaSessionRecords);
                        }

                        // Start Availability Users
                        $rolesId = [
                            config('constant.roles.speciality_lead'),
                            config('constant.roles.staff_coordinator'),
                            config('constant.roles.anesthetic_lead'),
                        ];

                        $availabilityUsers = $existingRota->hospitalDetail->users()
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

                            foreach($rotaSession->users as $user){
                                $subject = "Upcoming Session";
                                Mail::to($user->user_email)->send(new RotaSessionMail($subject, $user, $rotaSession));
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
                'message' => trans('messages.record_updated_successfully'),
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)
                ->respondWithError(trans('messages.error_message').$e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }


    public function getQuarters(){
    
        $currentYear = date('Y');
        
        // Retrieve quarters for the current year
        $quarters = Quarter::select('id','quarter_name','start_date','end_date')->whereYear('start_date', $currentYear)
                        ->whereYear('end_date', $currentYear)
                        ->get();

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $quarters,
        ])->setStatusCode(Response::HTTP_OK);
    }


    public function getAvailableSessions()
    {
        try {
            $user = auth()->user();

            $query = RotaSession::query();

            if ($user->is_speciality_lead) {
                $query->where('procedure_id', $user->specialityDetail->value('id'));
            }

            $sessions = $query->get();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.get_available_session_list'),
                'data'      => $sessions,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    public function updateAvailability(UpdateRotaSessionRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
        try {
            DB::beginTransaction();
            foreach ($validatedData['sessions'] as $session) {
                $rotaSession = RotaSession::findOrFail($session['rota_session_id']);

                // Ensure the user has access to the session
                if ($user->is_speciality_lead && $rotaSession->speciality_id !== $user->specialityDetail->value('id')) {
                    return $this->setStatusCode(400)->respondWithError(trans('auth.failed'));
                }

                // Check if the room, time slot, and date match the rota session
                if (
                    $rotaSession->room_id !== $session['room_id'] || $rotaSession->time_slot !== $session['time_slot'] || $rotaSession->scheduled->format('Y-m-d') !== $session['date']
                ) {
                    return $this->setStatusCode(500)->respondWithError(trans('messages.data_mismatch'));
                }

                // Validate date if 'scheduled' is not null
                // Validate date if 'scheduled' is not null
                if ($rotaSession->scheduled) {
                    $scheduledDate = $rotaSession->scheduled->format('Y-m-d');
                } else {
                    $scheduledDate = null;
                }

                if ($scheduledDate !== $session['date']) {
                    return $this->setStatusCode(500)->respondWithError(trans('messages.date_data_mismatch'));
                }

                // Check if the user is associated with the rota session
                $exists = $rotaSession->users()->where('user_id', $user->id)->exists();
                if (!$exists) {
                    return $this->setStatusCode(500)->respondWithError(trans('messages.user_not_assign_to_session'));
                }

                $rotaSession->users()->updateExistingPivot($user->id, ['status' => $session['status'] ?? "pending"]);
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_available_session_status')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating availability status'
            ], 500);
        }
    }
}
