<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;


class UserController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'filter_value'   => 'nullable|array',
            'filter_value.*' => 'integer', 
        ]);

        try {
            DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

            $user = auth()->user();
           
            $model = User::query()->with(['role:id,role_name'])->select('id', 'uuid', 'full_name', 'primary_role');

            //Start Apply filters
            if ($request->search) {

                $searchValue = trim($request->search);

                $model = $model->where(function ($query) use ($searchValue) {

                    $query->where('full_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('role', 'role_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('specialityDetail', 'speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('subSpecialityDetail', 'sub_speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('trusts', 'trust_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('getHospitals', 'hospital_name', 'like', '%' . $searchValue . '%');
                });
            }

            if ($request->filter_by) {

                if ($request->filter_by == 'role' && $request->filter_value) {

                    $model->whereRelation('role', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });

                } else if ($request->filter_by == 'speciality' && $request->filter_value) {

                    $model->whereRelation('specialityDetail', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });

                } else if ($request->filter_by == 'sub_speciality' && $request->filter_value) {

                    $model->whereRelation('subSpecialityDetail', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });

                } else if ($request->filter_by == 'hospital' && $request->filter_value) {

                    $model->whereRelation('getHospitals', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });

                }
            }
            //End Apply filters

            if ($user->is_trust_admin) {

                $trustIds = $user->trusts()->select('trust_id')->groupBy('trust_id')->pluck('trust_id')->toArray();

                $model = $model->whereRelation('trusts', function ($query) use ($trustIds) {
                    $query->whereIn('trust.id', $trustIds);
                });

            } else if ($user->is_hospital_admin) {

                $hospital_ids = $user->getHospitals()->select('hospital_id')->groupBy('hospital_id')->pluck('hospital_id')->toArray();

                $model = $model->whereRelation('getHospitals', function ($query) use ($hospital_ids) {
                    $query->whereIn('hospital.id', $hospital_ids);
                });
            }

            $getAllRecords = $model->where(function ($qu) {
                $qu->whereRelation('role', 'id', '!=', config('constant.roles.system_admin'))
                    ->whereRelation('role', 'id', '!=', auth()->user()->role->id);
            })->orderBy('created_at', 'desc')->paginate(10);

            if ($getAllRecords->count() > 0) {
                foreach ($getAllRecords as $record) {
                    $record->full_name = ucwords($record->full_name);
                    $record->speciality =   $record->specialityDetail()->value('speciality_name');
                    $record->sub_speciality = $record->subSpecialityDetail()->value('sub_speciality_name');
                    $record->trust = $record->trusts()->pluck('trust_name', 'id')->toArray();
                    $record->hospitals = $record->getHospitals()->pluck('hospital_name')->toArray();
                }
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $getAllRecords,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            dd($e->getMessage().'->'.$e->getLine());
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

            $trustId = $this->getTrustId($request);
            $user->getHospitals()->attach($request->hospital, ['trust_id' => $trustId]);

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
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
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

                $user_details['hospital'] = $user->getHospitals()->pluck('hospital_name', 'id')->toArray();

                $user_details['trust'] = $user->trusts ? $user->trusts()->value('id') : null;
                $user_details['trust_name'] = $user->trusts ? $user->trusts()->value('trust_name') : null;

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
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());         
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
                'user_email'   => $request->user_email ?? $user->user_email,
                'password'     => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $trustId = $this->getEditUserTrustId($request, $user);
            $user->getHospitals()->detach();
            $user->getHospitals()->attach($request->hospital, ['trust_id' => $trustId]);

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
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $uuid)
    {
        $request->validate([
            'confirm_password' => ['required', 'string', 'min:8'],
        ]);
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();

            $type = $request->type;
            $confirmPassword = $request->confirm_password;

            if ($user) {
                if ($type == 'confirm') {
                    if (!Hash::check($confirmPassword, $user->password)) {
                        return $this->setStatusCode(500)->respondWithError(trans('messages.invalid_password'));
                    }
                }

                $user->delete();
                auth()->logout();
                JWTAuth::invalidate(JWTAuth::getToken());
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_deleted_successfully'),
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    public function exportUserData(Request $request)
    {       
        $user = auth()->user();
        // Export users based on role
        return Excel::download(new UsersExport($user), 'users.xlsx');
    }

    private function getTrustId(Request $request)
    {
        $user = Auth::user();
        $tid = $user->getHospitals->value('trust');

        if ($user->is_system_admin) {
            $trustId = $request->trust;
        } elseif ($user->is_trust_admin) {
            $trustId = $request->filled('trust') ? $request->trust : $user->id;
        } elseif ($user->is_hospital_admin) {
            $trustId = $tid;
        }

        return $trustId;
    }

    private function getEditUserTrustId(Request $request, $user)
    {
        $authUser = Auth::user();
        $tid = $user->getHospitals->value('trust');
        if ($authUser->is_system_admin) {
            $trustId = $user ?  $request->trust : $tid;
        } else {
            $trustId = $tid;
        }

        return $trustId;
    }
}
