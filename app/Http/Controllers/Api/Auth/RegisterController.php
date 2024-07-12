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
            'user_email'        => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,email,NULL,id,deleted_at,NULL'],
            'password'          => ['required', 'string', 'min:8','confirmed'],
            'trust'             => ['required','exists:trust,id'],
            'role'              => ['required','exists:roles,id']
        ],[
            'profile_image.image' =>'Please upload image.',
            'profile_image.mimes' =>'Please upload image with extentions: jpeg,png,jpg.',
            'profile_image.max' =>'The image size must equal or less than '.config('constant.profile_max_size_in_mb'),
        ],[
            'full_name'  => 'name',
            'user_email' => 'email'
        ]);
        
        try {

            DB::beginTransaction();

            $user = User::create([
                'primary_role' => $request->role,
                'full_name'    => $request->full_name,
                'user_email'   => $request->user_email,
                'password'     => Hash::make($request->password),
            ]);
            
         /*
            if($request->has('profile_image')){
                $uploadId = null;
                $actionType = 'save';
                if($profileImageRecord = $user->profileImage){
                    $uploadId = $profileImageRecord->id;
                    $actionType = 'update';
                }
                uploadImage($user, $request->profile_image, 'user/profile-images',"user_profile", 'original', $actionType, $uploadId);
            }
          */
            
            DB::commit();
            
            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.register_success')
            ])->setStatusCode(Response::HTTP_OK);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            // return $this->throwValidation([$e->getMessage()]);
            return $this->throwValidation([trans('messages.error_message')]);
        }
    }

   


}
