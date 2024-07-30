<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Models\Role;
use App\Models\User;
use App\Models\Trust;
use App\Models\Hospital;
use App\Models\Speciality;
use App\Models\SubSpeciality;
use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use App\Rules\TitleValidationRule;


class HomeController extends APIController
{
    public function getRoles(){
        $user = Auth::User();
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

        if(is_null($trust)){
            $hospitals = Hospital::pluck('hospital_name','id');
        }else if(auth()->user()){

            if(auth()->user()->is_trust_admin || auth()->user()->is_hospital_admin){
                $hospitals = auth()->user()->getHospitals()->pluck('hospital_name','id');
            }else{
                $hospitals = Hospital::where('trust',$trust)->pluck('hospital_name','id');
            } 
            
        }

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
            'user_email'        => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,user_email,'.$authUser->id.',id,deleted_at,NULL'],
            'speciality'        => ['required','exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['required','exists:sub_speciality,id,deleted_at,NULL'],
        ];

        if($request->password){
            $validateData['password']   = ['nullable', 'string', 'min:8'];
        }

        $request->validate($validateData,[],[
            'full_name'  => 'name',
            'user_email' => 'email'
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


            $specialities = [
                $request->speciality => ['sub_speciality_id' => $request->sub_speciality],
            ];
            
            // Sync specialities with additional pivot data
            auth()->user()->specialityDetail()->sync($specialities);
            
            // Fetch the updated user data
            $user = User::with([
                'role:id,role_name',
                'trusts:id,trust_name',
                'getHospitals:id,hospital_name',
                'specialityDetail:id,speciality_name',
                'subSpecialityDetail:id,sub_speciality_name'
            ])->findOrFail($authUser->id);

            $reponseData = [
                'data'          => [
                    'uuid'                  => $user->uuid,
                    'full_name'             => $user->full_name,
                    'user_email'            => $user->user_email,
                    'primary_role'          => $user->primary_role,
                    'role'                  => $user->role->role_name,
                    'trust'                 => $user->trusts ? $user->trusts()->value('id') : null,
                    'trust_name'            => $user->trusts ? $user->trusts()->value('trust_name') : null,
                    'hospital'              => $user->getHospitals()->pluck('hospital_name', 'id')->toArray(),
                    'speciality'            => $user->specialityDetail()->value('speciality_name'),
                    'sub_speciality'        => $user->subSpecialityDetail()->value('sub_speciality_name'),
                ]
            ];

            DB::commit();
            
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.profile_updated_successfully'),
                'data'     => $reponseData
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::info($e->getMessage().' '.$e->getFile().' '.$e->getLine());
         
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());

            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

    }


}