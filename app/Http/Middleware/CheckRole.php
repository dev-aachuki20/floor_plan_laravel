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
       
        if (!in_array(auth()->user()->role->id,$roles)) {

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to access this resource.'
            ], 403);
           
        }

        return $next($request);
    }
}
