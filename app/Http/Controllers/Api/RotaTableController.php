<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\RotaSession;
use App\Models\SessionStatus;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Http\Requests\RotaTable\StoreRequest;
use App\Http\Requests\RotaTable\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use App\Models\Procedure;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $request->validate([
            'hospital' => ['required', 'integer'],
        ]);

        $hospital = $request->hospital;

        $hospital = Hospital::select('id', 'hospital_name')->where('id', $hospital)->first();
        $hospital->rooms = $hospital->rooms()->select('id', 'room_name')->get();
        foreach ($hospital->rooms as $room) {
            $room->time_slots = config('constant.time_slots');

            // Fetched all procedures according to the hospital.
            // $room->procedures = Procedure::where('hospital_id', $hospital->id)->select('id', 'procedures_name')->get();

            // Get current date and the next 7 days
            $currentDate = Carbon::now();
            $weekDates = [];
            for ($i = 0; $i < 7; $i++) {
                $weekDates[] = [
                    'date' => $currentDate->copy()->addDays($i)->format('Y-m-d'),
                    'day_name' => $currentDate->copy()->addDays($i)->format('l')
                ];
            }
            $room->week_dates = $weekDates;
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
