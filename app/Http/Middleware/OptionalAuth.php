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
    public function handle(Request $request, Closure $next, $guard = null)
    {
        dd('dd');
        try {
            if ($request->bearerToken()) {
                Auth::guard($guard ?: 'api')->onceUsingId(
                    optional(Auth::guard($guard ?: 'api')->user())->id
                );
            }
        } catch (\Throwable $e) {
            // If token invalid or expired, just ignore and continue as guest
        }

        return $next($request);
    }
}
