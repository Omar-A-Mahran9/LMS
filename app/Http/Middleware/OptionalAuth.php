<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalAuth
{
    /**
     * Handle an incoming request.
     *
     * This middleware tries to authenticate the user if a token exists,
     * but does not fail if no token is provided.
     */
    public function handle(Request $request, Closure $next, $guard = 'api')
    {
        Auth::shouldUse($guard);

        if ($request->bearerToken()) {
            try {
                Auth::authenticate(); // will throw if invalid/expired
            } catch (\Throwable $e) {
                // Invalid or expired token → continue as guest
            }
        }

        return $next($request);
    }
}
