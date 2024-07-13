<?php

namespace App\Http\Controllers\Api\Auth;

use DB;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends APIController
{
    
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'full_name'         => ['required','string','max:255'],
            'user_email'        => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,user_email,NULL,id,deleted_at,NULL'],
            'password'          => ['required', 'string', 'min:8'],
            'trust'             => ['required','exists:trust,id'],
            'role'              => ['required','exists:roles,id'],
            'hospital'          => ['required','exists:hospital,id,deleted_at,NULL'],
            'speciality'        => ['required','exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['required','exists:sub_speciality,id,deleted_at,NULL'],

        ],[],[
            'full_name'  => 'name',
            'user_email' => 'email'
        ]);
        
        try {

            DB::beginTransaction();

            $user = User::create([
                'primary_role' => $request->role,
                'hospital'     => $request->hospital,
                'full_name'    => $request->full_name,
                'user_email'   => $request->user_email,
                'password'     => Hash::make($request->password),
            ]);

            //Verification mail sent
            $user->NotificationSendToVerifyEmail();
            
            $specialities = [
                $request->speciality => ['sub_speciality_id' => $request->sub_speciality],
            ];
            
            // Sync specialities with additional pivot data
            $user->specialities()->sync($specialities);

            DB::commit();
            
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.register_success')
            ])->setStatusCode(Response::HTTP_OK);
            
        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::info($e->getMessage().' '.$e->getFile().' '.$e->getLine());
         
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    public function verifyEmail($uuid, $hash){
        $user = User::where('uuid',$uuid)->first();
        
        if(!is_null($user->email_verified_at)){
            
            return $this->respondOk([
                'status'   => true,
                'message'  => 'Email is already verifed!',
            ])->setStatusCode(Response::HTTP_OK);
           
        }

        if ($user && $hash === sha1($user->user_email)) {
            $user->update(['email_verified_at' => date('Y-m-d H:i:s')]);

            return $this->respondOk([
                'status'   => true,
                'message'  => 'Email verified successfully!',
            ])->setStatusCode(Response::HTTP_OK);

        }

        return $this->setStatusCode(401)->respondWithError('Mail verification failed!');

    }
   


}
