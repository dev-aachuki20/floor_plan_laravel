<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Rules\IsActive;
use App\Rules\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class LoginController extends Controller
{
    
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request){
        $credentialsOnly = $request->validate([
            'email'    => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','exists:users,email,deleted_at,NULL', new UserHasRole([config('constant.roles.admin'),config('constant.roles.staff')], $request->email),new IsActive],
            'password' => 'required|min:8',
        ],[
            'email.exists' => trans('validation.invalid'),
        ]);


        $user = User::where('email',$request->email)->first();
       
        if($user){
            
            $remember_me = !is_null($request->remember_me) ? true : false;
            if (Auth::attempt($credentialsOnly, $remember_me))
            {  
                $routeName = 'admin.dashboard';

                if(auth()->user()->is_company){
                    $routeName = 'home';
                }

                return redirect()->route($routeName)->with('success',trans('auth.messages.login.success'));
            }

            
            $loginRouteName = 'admin.login';
            if($user->is_company){
                $loginRouteName = 'login';
            }

            return redirect()->route($loginRouteName)->with('error',trans('auth.failed'));

        }
    }

    public function logout()
    {
        $routeName = 'admin.login';

        if(auth()->user()->is_company){
            $routeName = 'login';
        }
        Auth::guard('web')->logout();
        return redirect()->route($routeName);
    }


    public function showAdminLogin(){
        return view('backend.auth.login');
    }

}
