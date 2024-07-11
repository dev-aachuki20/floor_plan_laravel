<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();

        if (!array_intersect($roles, $userRoles)) {

            if($user->is_admin || $user->is_staff){
                return redirect()->route('admin.login');
            }elseif($user->is_company){
                return redirect()->route('login');
            }else{
                return abort(403);
            }
           
        }

        return $next($request);
    }
}
