<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Models\Role;
use App\Models\User;
use App\Models\Trust;
use App\Models\Hospital;
use App\Models\Speciality;
use App\Models\SubSpeciality;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends APIController
{
    public function getRoles(){
        $roles = Role::where('id','!=',config('constant.roles.system_admin'))->pluck('role_name','id');

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $roles,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getTrusts(){

        $trusts = Trust::pluck('trust_name','id');

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $trusts,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getHospitals($trust){

        $hospitals = Hospital::where('trust',$trust)->pluck('hospital_name','id');
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $hospitals,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getSpecialities(){
        $specialities = Speciality::pluck('speciality_name','id');
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $specialities,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getSubSpecialities($speciality){

        $subSpecialities = SubSpeciality::where('parent_speciality_id',$speciality)->pluck('sub_speciality_name','id');
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $subSpecialities,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function updateProfile(Request $request){

        $request->validate([
            
            'full_name'         => ['required','string','max:255'],
            // 'user_email'        => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,user_email,NULL,id,deleted_at,NULL'],
            'password'          => ['required', 'string', 'min:8'],
            'trust'             => ['required','exists:trust,id'],
            // 'role'              => ['required','exists:roles,id'],
            'hospital'          => ['required','exists:hospital,id,deleted_at,NULL'],
            'speciality'        => ['required','exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['required','exists:sub_speciality,id,deleted_at,NULL'],

        ],[],[
            'full_name'  => 'name',
            'user_email' => 'email'
        ]);

        try {

            DB::beginTransaction();

            $updateRecords = [
                // 'primary_role' => $request->role,
                'hospital'     => $request->hospital,
                'full_name'    => ucwords($request->full_name),
                // 'user_email'   => $request->user_email,
                'password'     => Hash::make($request->password),
            ];

            $user = User::where('id',auth()->user()->id)->update($updateRecords);


            $specialities = [
                $request->speciality => ['sub_speciality_id' => $request->sub_speciality],
            ];
            
            // Sync specialities with additional pivot data
            auth()->user()->specialities()->sync($specialities);

            DB::commit();
            
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.profile_updated_successfully')
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::info($e->getMessage().' '.$e->getFile().' '.$e->getLine());
         
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

    }


}