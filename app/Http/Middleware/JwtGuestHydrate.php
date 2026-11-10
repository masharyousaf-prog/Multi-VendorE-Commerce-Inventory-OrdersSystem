<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtGuestHydrate
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken() && $request->hasCookie('jwt_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('jwt_token'));
        }

        try {
            if ($request->bearerToken()) {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user && $user->is_active) {
                    Auth::setUser($user);
                }
            }
        } catch (\Throwable $e) {
            // Continue as guest if token is missing/expired on public routes
        }

        return $next($request);
    }
}
