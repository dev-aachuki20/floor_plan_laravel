<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogoutUserFromOtherDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($this->isApiRequest($request)) {
            $deviceID = $request->header('X-Device-Id');

            if ($user && $user->current_session_id !== $deviceID) {
                $tokenId = $request->user()->currentAccessToken()->id;
                $user->tokens()->where('id', $tokenId)->delete();

                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } else {
            if ($user && $user->current_session_id !== session()->getId()) {
                Auth::logout();
                if($request->ajax()){
                    return response()->json(['message' => 'Unauthorized'], 401);
                } else {
                    return to_route('login');
                }
            }
        }
        return $next($request);
    }

    private function isApiRequest(Request $request)
    {
        return strpos($request->path(), 'api/') === 0
            || $request->is('api/*')
            || $request->header('Accept') === 'application/json';
    }
}
