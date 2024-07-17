<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;


class UserController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $model = User::query()->with(['role:id,role_name', 'hospitalDetail:id,hospital_name'])->select('id', 'uuid', 'full_name', 'primary_role', 'hospital');

            //Start Apply filters
            if ($request->search) {

                $searchValue = trim($request->search);

                $model = $model->where(function ($query) use ($searchValue) {

                    $query->where('full_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('role', 'role_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('specialityDetail', 'speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('subSpecialityDetail', 'sub_speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('hospitalDetail', 'hospital_name', 'like', '%' . $searchValue . '%');
                });
            }

            if ($request->filter_by) {

                if ($request->filter_by == 'role' && $request->filter_value) {

                    $model = $model->whereRelation('role', 'id', '=', $request->filter_value);
                } else if ($request->filter_by == 'speciality' && $request->filter_value) {

                    $model = $model->whereRelation('specialityDetail', 'id', '=', $request->filter_value);
                } else if ($request->filter_by == 'sub_speciality' && $request->filter_value) {

                    $model = $model->whereRelation('subSpecialityDetail', 'id', '=', $request->filter_value);
                }
            }
            //End Apply filters



            $getAllRecords = $model->where(function ($qu) {

                $qu->whereRelation('role', 'id', '!=', config('constant.roles.system_admin'))
                    ->whereRelation('role', 'id', '!=', auth()->user()->role->id);
            })->orderBy('created_at', 'desc')->paginate(10);

            if ($getAllRecords->count() > 0) {

                foreach ($getAllRecords as $record) {
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
    public function store(StoreRequest $request)
    {
        try {

            DB::beginTransaction();

            $user = User::create([
                'primary_role' => $request->role,
                'full_name'    => $request->full_name,
                'user_email'   => $request->user_email,
                'password'     => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            //Verification mail sent
            // $user->NotificationSendToVerifyEmail();

            // Attach hospitals and trust to the user
            $user->hospitals()->attach($request->hospital, ['trust_id' => $request->trust]);


            $specialities = [
                $request->speciality => ['sub_speciality_id' => $request->sub_speciality],
            ];

            // Sync specialities with additional pivot data
            $user->specialityDetail()->sync($specialities);

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_created_successfully')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::info($e->getMessage().' '.$e->getFile().' '.$e->getLine());         
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        try {
            $user_details = [];
            $user = User::where('uuid', $uuid)->firstOrFail();

            if ($user) {
                $user_details['uuid']          = $user->uuid;
                $user_details['full_name']     = ucwords($user->full_name);
                $user_details['primary_role']  = $user->primary_role;
                $user_details['role_name']     = $user->role->role_name;
                $user_details['user_email']    = $user->user_email;
                $user_details['phone']         = $user->phone;


                // $user_details['trust']         = $user->hospitalDetail ? $user->hospitalDetail->trust : null;
                // $user_details['trust_name']    = $user->hospitalDetail ? $user->hospitalDetail->trustDetails->trust_name : null;

                // $user_details['hospital']      = $user->hospitals;
                // $user_details['hospital_name'] = $user->hospitalDetail ? $user->hospitalDetail->hospital_name : null;


                $user_details['speciality']      = $user->specialityDetail()->value('id');
                $user_details['speciality_name'] = $user->specialityDetail()->value('speciality_name');

                $user_details['sub_speciality']      =  $user->subSpecialityDetail()->value('id');
                $user_details['sub_speciality_name'] = $user->subSpecialityDetail()->value('sub_speciality_name');

                $user_details['created_by']    = $user->createdBy ? $user->createdBy->full_name : null;
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_record_retrieved_successfully'),
                'data'      => $user_details,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());         
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $uuid)
    {
        try {
            DB::beginTransaction();

            $user = User::where('uuid', $uuid)->firstOrFail();

            $user->update([
                'full_name'    => $request->full_name ?? $user->full_name,
                'password'     => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $user->hospitals()->detach();
            $user->hospitals()->attach($request->hospital, ['trust_id' => $request->trust]);

            // Sync speciality and sub_speciality
            $specialities = [
                $request->speciality => ['sub_speciality_id' => $request->sub_speciality],
            ];
            $user->specialityDetail()->sync($specialities);

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'  => trans('messages.user_updated_successfully')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());         
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();
            $user->hospitals()->detach();
            $user->delete();
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_deleted_successfully'),
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }
}
