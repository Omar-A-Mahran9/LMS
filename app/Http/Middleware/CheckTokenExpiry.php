<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token && $token->expires_at && now()->greaterThan($token->expires_at)) {
            $token->delete();

            return response()->json([
                'message' => 'Your session has expired. Please log in again.',
            ], 401);
        }

        return $next($request);
    }
}
