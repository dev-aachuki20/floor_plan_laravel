<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Api\APIController;

class PasswordResetController  extends APIController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate(['user_email' => [
            'required',
            'email',
            'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i',
            'exists:users,user_email']
        ],
        getCommonValidationRuleMsgs(),
        [
            'user_email' => 'email',
        ]);


        try{

            DB::beginTransaction();

            $email_id = $request->user_email;

            //Check deleted User
            $deletedUser = User::where('user_email', $email_id)->onlyTrashed()->first();
            if ($deletedUser && $deletedUser->deleted_at) {
                return $this->setStatusCode(403)->respondWithError(trans('auth.account_suspended'));
            }
            //End Check deleted User

            $token = generateRandomString(64);

            $user = User::where('user_email',$email_id)->first();

            $reset_password_url = config('app.site_url').'/reset-password?token='.$token;

            DB::table('password_reset_tokens')
            ->where('email', $email_id)
            ->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $email_id,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            $subject = 'Reset Password Notification';
            Mail::to($email_id)->queue(new ResetPasswordMail($user->full_name,$reset_password_url,$subject));

            \Log::info('Sent Forgot Password Mail');

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message' => trans('passwords.sent'),
            ])->setStatusCode(200);

        }catch(\Exception $e){
            DB::rollBack();
            \Log::info('Error in PasswordResetController::sendResetLinkEmail (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    public function resetPassword(Request $request){

        $validated = $request->validate([
            'token'    => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',

        ], getCommonValidationRuleMsgs());

        try{
            DB::beginTransaction();
            $updatePassword = DB::table('password_reset_tokens')->where(['token' => $request->token])->first();
            if(!$updatePassword){

                return $this->throwValidation([trans('passwords.token')]);

            }else{

                $email_id = $updatePassword->email;

                //Check deleted User
                $deletedUser = User::where('user_email', $email_id)->onlyTrashed()->first();
                if ($deletedUser && $deletedUser->deleted_at) {
                    return $this->setStatusCode(403)->respondWithError(trans('auth.account_suspended'));
                }
                //End Check deleted User

                $user = User::where('user_email', $email_id)
                ->update(['password' => Hash::make($request->password)]);

                DB::table('password_reset_tokens')->where(['email'=> $email_id])->delete();

                DB::commit();

                return $this->respondOk([
                    'success'   => true,
                    'message' => trans('passwords.reset'),
                ])->setStatusCode(200);

            }
        }catch(\Exception $e){
            DB::rollBack();
            \Log::info('Error in PasswordResetController::resetPassword (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


}
