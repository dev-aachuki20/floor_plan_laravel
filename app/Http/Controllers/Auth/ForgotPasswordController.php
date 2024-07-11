<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\IsActive;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
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

    public function index()
    {
        return view('auth.forgot-password');
    }

    public function showAdminForgetPassword()
    {
        return view('backend.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate(['email' => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','exists:users,email,deleted_at,NULL',new IsActive]], getCommonValidationRuleMsgs());
        DB::beginTransaction();
        try{
            $user = User::where('email',$request->email)->first();
            if($user){
                $token = generateRandomString(64);
                $email_id = $request->email;

                $reset_password_url = route('admin.resetPassword',['token'=>$token]);

                if($user->is_company){
                    $reset_password_url = route('resetPassword',['token'=>$token]);
                }
                
                DB::table('password_reset_tokens')
                ->where('email', $email_id)
                ->delete();

                DB::table('password_reset_tokens')->insert([
                    'email' => $email_id,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

                $subject = 'Reset Password Notification';
                Mail::to($email_id)->send(new ResetPasswordMail($user->name,$reset_password_url,$subject));

                DB::commit();

                return redirect()->back()->with('success',trans('passwords.sent'));

            }else{
                // Set Flash Message
                return redirect()->back()->withErrors(['email' => trans('messages.invalid_email')])->withInput($request->only('email'));

            }

        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage().'->'.$e->getLine());
            return redirect()->back()->with('error',trans('messages.error_message'));
        }
    }


}
