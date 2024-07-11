<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // return $request->expectsJson() ? null : route('login');

        if ($request->expectsJson()) {
            return null;
        }

        // Check if the route name starts with 'admin.'
        if (Route::is('admin.*')) {
            return route('admin.login');
        }

        // Default to frontend login route
        return route('login');
    }
}
