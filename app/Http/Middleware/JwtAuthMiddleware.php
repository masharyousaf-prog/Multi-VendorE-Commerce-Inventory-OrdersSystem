<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            // Allow token from Authorization Bearer header OR HttpOnly Cookie
            if (!$request->bearerToken() && $request->hasCookie('jwt_token')) {
                $request->headers->set('Authorization', 'Bearer ' . $request->cookie('jwt_token'));
            }

            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();

            if (!$user || !$user->is_active) {
                return $this->unauthorizedResponse($request, 'Your account has been suspended.');
            }

            // Bind authenticated user to Auth facade for Blade templates & Controllers
            Auth::setUser($user);

            // Check Role Claim directly from the signed JWT payload
            if (!empty($roles)) {
                $tokenRole = $payload->get('role') ?? $user->role;
                if (!in_array($tokenRole, $roles, true)) {
                    if ($request->expectsJson()) {
                        return response()->json(['error' => 'Forbidden: Insufficient JWT role privileges.'], 403);
                    }
                    abort(403, 'Unauthorized access for your account role.');
                }
            }
        } catch (JWTException $e) {
            return $this->unauthorizedResponse($request, 'Session expired or invalid JWT token. Please log in.');
        }

        return $next($request);
    }

    protected function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => $message], 401);
        }

        return redirect()->route('login')
            ->withCookie(cookie()->forget('jwt_token'))
            ->withErrors(['email' => $message]);
    }
}
