<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    public function showform(Request $request)
    {
        return view('auth.reset-password')->with(['token' =>$request->token]);
    }

    public function showAdminResetPassword(Request $request)
    {
        return view('backend.auth.reset-password')->with(['token' =>$request->token]);
    }

    // Reset the given user's password.
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',

        ], getCommonValidationRuleMsgs());

        DB::beginTransaction();
        try{
            $updatePassword = DB::table('password_reset_tokens')->where(['token' => $request->token])->first();
            if(!$updatePassword){
                return redirect()->back()->with('error', trans('passwords.token'))->withInput($request->all());
            }else{
                $email_id = $updatePassword->email;
                $retriveUser = User::where('email',$email_id)->first();
                if($retriveUser->status == 1){
                    $user = User::where('email', $email_id)
                    ->update(['password' => Hash::make($request->password)]);

                    DB::table('password_reset_tokens')->where(['email'=> $email_id])->delete();

                    $routeName = 'admin.login';
                    if($retriveUser->is_company){
                        $routeName = 'login';
                    }

                    DB::commit();
                    return redirect()->route($routeName)->with('success',trans('passwords.reset'));

                }else{
                    return redirect()->back()->withErrors(['error' => trans('passwords.suspened')])->withInput($request->all());
                }

            }
        }catch(\Exception $e){
            DB::rollBack();
            dd($e->getMessage().'->'.$e->getLine());
            return redirect()->back()->with('error',trans('messages.error_message'));
        }
    }
    


}
