<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectApiBrowserRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the request is for an API endpoint and from a browser
        if ($request->is('api/*') && !$request->expectsJson()) {

            // Redirect to the desired URL if it's a browser request
            return redirect(config('app.site_url').'/login');
        }

        // Otherwise, continue with the request
        return $next($request);
    }
}
