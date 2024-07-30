<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\RotaSession;
use App\Models\Quarter;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Http\Requests\RotaTable\StoreRequest;
use App\Http\Requests\RotaTable\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        // Validate the input
        $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer'],
        ]);

        $hospitalId = $request->hospital;
        $weekDays   = $request->week_days;

       
        $hospital = Hospital::select('id', 'hospital_name')->where('id', $hospitalId)->first();
        $hospital->rooms = $hospital->rooms()->select('id', 'room_name')->get();

        $timeSlots = config('constant.time_slots');

        foreach ($hospital->rooms as $room) {
           
            $recordsByDateAndTimeSlot = [];

            foreach ($weekDays as $date) {
                $recordsByDateAndTimeSlot[$date] = [];

                foreach ($timeSlots as $timeSlot) {
                    
                    RotaSession

                    $record = DB::table('records_table') 
                        ->where('room_id', $room->id)
                        ->whereDate('created_at', $date)
                        ->where('time_slot', $timeSlot) 
                        ->first();

                    // Add record or null to the array
                    $recordsByDateAndTimeSlot[$date][$timeSlot] = $record ? $record : null;
                }
            }

            // Assign the records to the room
            $room->recordsByDateAndTimeSlot = $recordsByDateAndTimeSlot;
        }


        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $hospital,
        ])->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreRequest $request)
    {
        dd($request->all());
        try {
            DB::beginTransaction();

            // Validate the incoming request data
            $validatedData = $request->validated();

            // Initialize an empty array to store multiple rota session entries
            $rotaSessions = [];

            // Iterate over each room in the validated data
            foreach ($validatedData['rooms'] as $roomId => $roomData) {
                // Iterate over each time slot for the current room
                foreach ($roomData['time_slots'] as $timeSlot => $slotData) {
                    // Prepare a rota session entry
                    $rotaSessions[] = [
                        'hospital_id'          => $validatedData['hospital_id'],
                        'user_id'              => $validatedData['user_id'],
                        'procedure_id'         => $slotData['procedure_id'],
                        'room_id'              => $roomId,
                        'time_slot'            => $timeSlot,
                        'status_id'            => $validatedData['status_id'],
                        'session_description'  => $roomData['session_description'],
                        'session_released'     => $roomData['session_released'],
                    ];
                }
            }

            // Insert all rota session entries into the database
            RotaSession::insert($rotaSessions);

            // Commit the transaction
            DB::commit();

            return $this->respondOk([
                'status'    => true,
                'message'   => trans('messages.record_created_successfully'),
                'data'      => $rotaSessions
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return $this->setStatusCode(500)
                ->respondWithError(trans('messages.error_message'));
        }
    }

    // public function store(StoreRequest $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $validatedData = $request->validated();
    //         $rota = RotaSession::create($validatedData);

    //         DB::commit();

    //         return $this->respondOk([
    //             'status'    => true,
    //             'message'   => trans('messages.record_created_successfully'),
    //             'data'      => $rota
    //         ])->setStatusCode(Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
    //         return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
    //     }
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $uuid)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $rota = RotaSession::where('uuid', $uuid)->first();
            $rota->update($validatedData);

            DB::commit();

            return $this->respondOk([
                'status'    => true,
                'message'   => trans('messages.record_updated_successfully'),
                'data'      => $rota
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    public function getQuarters(){
    
    $currentYear = date('Y');
    
    // Retrieve quarters for the current year
    $quarters = Quarter::whereYear('start_date', $currentYear)
                       ->whereYear('end_date', $currentYear)
                       ->get();
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
