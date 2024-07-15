<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class UserController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $model = User::query()->with(['role:id,role_name','hospitalDetail:id,hospital_name'])->select('id','uuid','full_name','primary_role','hospital');

            //Start Apply filters
            if ($request->search) {

                $searchValue = trim($request->search);

                $model = $model->where(function($query) use($searchValue){

                    $query->where('full_name', 'like', '%' . $searchValue . '%')
                    ->orWhereRelation('role','role_name','like','%'. $searchValue .'%')
                    ->orWhereRelation('specialityDetail','speciality_name','like','%'. $searchValue .'%')
                    ->orWhereRelation('subSpecialityDetail','sub_speciality_name','like','%'. $searchValue .'%')
                    ->orWhereRelation('hospitalDetail','hospital_name','like','%'. $searchValue .'%');

                });

            }

            if ($request->filter_by) {

                if($request->filter_by == 'role' && $request->filter_value){
                    
                    $model = $model->whereRelation('role','id','=',$request->filter_value);

                }else if($request->filter_by == 'speciality' && $request->filter_value){

                    $model = $model->whereRelation('specialityDetail','id','=',$request->filter_value);


                }else if($request->filter_by == 'sub_speciality' && $request->filter_value){

                    $model = $model->whereRelation('subSpecialityDetail','id','=',$request->filter_value);

                }

            }
            //End Apply filters



            $getAllRecords = $model->where(function($qu){
                
                $qu->whereRelation('role','id','!=',config('constant.roles.system_admin'))
                ->whereRelation('role','id','!=',auth()->user()->role->id);

            })->orderBy('created_at', 'desc')->paginate(10);
            
            if ($getAllRecords->count() > 0) {

                foreach($getAllRecords as $record){
                    $record->full_name = ucwords($record->full_name);
                    // $record->role_name = $record->role->role_name;
                    // $record->hospital  = $record->hospitalDetail ? $record->hospitalDetail->hospital_name : null;
                    $record->speciality = $record->specialityDetail()->value('speciality_name');
                    $record->sub_speciality = $record->subSpecialityDetail()->value('sub_speciality_name');
                }
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $user,
        ])->setStatusCode(Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($uuid)
    {
        dd($uuid);
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->delete();

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_deleted_successfully'),
        ])->setStatusCode(Response::HTTP_OK);
    }
}
