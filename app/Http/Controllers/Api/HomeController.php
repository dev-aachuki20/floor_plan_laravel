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
use Illuminate\Support\Facades\Hash;
use App\Rules\TitleValidationRule;


class HomeController extends APIController
{
    public function getRoles(){
        $user = auth()->user();
        if ($user->is_trust_admin) {
            $roles = Role::whereNotIn('id', [config('constant.roles.system_admin'), config('constant.roles.trust_admin')])->pluck('role_name', 'id');
        }else if ($user->is_hospital_admin) {
            $roles = Role::whereNotIn('id', [config('constant.roles.system_admin'), config('constant.roles.trust_admin'), config('constant.roles.hospital_admin')])->pluck('role_name', 'id');
        } else {
            $roles = Role::where('id', '!=', config('constant.roles.system_admin'))->pluck('role_name', 'id');
        }

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

    public function getHospitals($trust=null){

        $hospitals = [];

        if(!auth()->user()->is_system_admin){
            $hospitals = auth()->user()->getHospitals()->pluck('hospital_name','id');
        }else if(is_null($trust)){
            $hospitals = Hospital::pluck('hospital_name','id');
        }else{
            $hospitals = Hospital::where('trust',$trust)->pluck('hospital_name','id');
        } 

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $hospitals,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getSpecialities($type = null){
        $specialities = Speciality::where('id','!=',config('constant.unavailable_speciality_id'))->pluck('speciality_name','id');
        
       if($type == 'list'){
            $specialities = Speciality::pluck('speciality_name','id');
       }
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $specialities,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getSubSpecialities($speciality=null){

        if(is_null($speciality)){

            $subSpecialities = SubSpeciality::pluck('sub_speciality_name','id');

        }else{

            $subSpecialities = SubSpeciality::where('parent_speciality_id',$speciality)->pluck('sub_speciality_name','id');

        }
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $subSpecialities,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function updateProfile(Request $request){

        $authUser = auth()->user();

        $validateData = [
            'full_name'         => ['required','string','max:255',new TitleValidationRule],
            'user_email'        => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,user_email,'.$authUser->id.',id'],
        ];

        if($request->password){
            $validateData['password']   = ['nullable', 'string', 'min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/'];
        }


        $request->validate($validateData,[
            'password.regex' => trans('messages.password_regex')
        ],[
            'full_name'  => 'name',
            'user_email' => 'email',
        ]);

        try {

            DB::beginTransaction();

            $updateRecords = [
                'full_name'    => ucwords($request->full_name),
                'user_email'   => $request->user_email,
            ];

            if($request->password){
                $updateRecords['password'] = Hash::make($request->password);
            }

            $user = User::where('id',auth()->user()->id)->update($updateRecords);
           
            // Fetch the updated user data
            $withRelation = [
                'role:id,role_name',
                'trusts:id,trust_name',
                'getHospitals:id,hospital_name',
                'specialityDetail:id,speciality_name',
                'subSpecialityDetail:id,sub_speciality_name'
            ];

            if($authUser->primary_role == config('constant.roles.booker')){

                $withRelation = [
                    'role:id,role_name',
                    'trusts:id,trust_name',
                    'getHospitals:id,hospital_name',
                ];
            }

            $user = User::with($withRelation)->findOrFail($authUser->id);

            $data = [
                'uuid'                  => $user->uuid,
                'full_name'             => $user->full_name,
                'user_email'            => $user->user_email,
                'primary_role'          => $user->primary_role,
                'role'                  => $user->role->role_name,
                'trust'                 => $user->trusts ? $user->trusts()->value('id') : null,
                'trust_name'            => $user->trusts ? $user->trusts()->value('trust_name') : null,
                'hospital'              => $user->getHospitals()->pluck('hospital_name', 'id')->toArray(),
                'is_tos'                => $user->is_tos,
            ];

            if($authUser->primary_role != config('constant.roles.booker')){
                $data['speciality'] = $user->specialityDetail()->value('speciality_name');
                $data['sub_speciality'] = $user->subSpecialityDetail()->value('sub_speciality_name');
            }


            $reponseData = [
                'data'          => $data
            ];

            DB::commit();
            
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.profile_updated_successfully'),
                'data'     => $reponseData
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info('Error in HomeController::updateProfile (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

    }


}