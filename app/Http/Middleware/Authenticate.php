<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('dashboard') || $request->is('dashboard/*'))
        {
            return route('admin.login-form');

        }
        if ($request->is('vendor') || $request->is('vendor/*'))
        {
            return route('vendor.login-form');
        }
        // else
        //     return redirect()->guest('/login');
    }
}