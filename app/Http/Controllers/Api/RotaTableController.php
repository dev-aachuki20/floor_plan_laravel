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

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;


class RotaTableController extends APIController
{
   public function index(Request $request){
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
                // foreach ($getAllRecords as $record) {
                //     $record->full_name = ucwords($record->full_name);
                //     $record->speciality =   $record->specialityDetail()->value('speciality_name');
                //     $record->sub_speciality = $record->subSpecialityDetail()->value('sub_speciality_name');
                //     $record->trust = $record->trusts()->pluck('trust_name', 'id')->toArray();
                //     $record->hospitals = $record->getHospitals()->pluck('hospital_name')->toArray();
                // }
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $getAllRecords,
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            // dd($e->getMessage().'->'.$e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

   }

   /**
    * Get rota table details
    */
    public function getDetails(Request $request){

        $request->validate([
            'hospital' => ['required', 'integer'],
        ]);

        $hospital = $request->hospital;
      
        $hospital = Hospital::select('id','hospital_name')->where('id',$hospital)->first();
        $hospital->rooms = $hospital->rooms()->select('id','room_name')->get();

        foreach($hospital->rooms as $room){
            $room->time_slots = config('constant.time_slots');

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
        dd('working on create');

        try {
            DB::beginTransaction();

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_created_successfully')
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
    * Update the specified resource in storage.
    */
    public function update(UpdateRequest $request, $uuid)
    {
        dd('working on edit');
        try {
            DB::beginTransaction();

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'  => trans('messages.user_updated_successfully')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }



}