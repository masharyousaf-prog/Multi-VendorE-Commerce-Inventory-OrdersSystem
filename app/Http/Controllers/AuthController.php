<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,vendor',
            'store_name' => 'nullable|required_if:role,vendor|string|max:120',
        ]);

        // Requirement 3: Users are approved by default (is_active = true)
        // Requirement 2: Removed NewUserRegistered admin notification
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'store_name' => $validated['role'] === 'vendor' ? ($validated['store_name'] ?? $validated['name'] . ' Store') : null,
            'store_slug' => $validated['role'] === 'vendor' ? Str::slug(($validated['store_name'] ?? $validated['name']) . '-' . Str::random(4)) : null,
        ]);

        // Requirement 1: Issue JWT Token immediately upon registration
        $token = JWTAuth::fromUser($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Registration successful',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $user,
            ], 201);
        }

        return $this->redirectByRole($user, $token, 'Welcome aboard! Your account is active and ready.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid email or password.'], 401);
            }
            return back()->withErrors(['email' => 'Invalid login credentials.'])->withInput();
        }

        $user = JWTAuth::user();

        if (!$user->is_active) {
            JWTAuth::invalidate($token);
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Your account has been suspended by an administrator.'], 403);
            }
            return back()->withErrors(['email' => 'Your account has been suspended by an administrator.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $user,
            ]);
        }

        return $this->redirectByRole($user, $token, 'Signed in via JWT authentication.');
    }

    public function logout(Request $request)
    {
        try {
            if ($request->hasCookie('jwt_token')) {
                JWTAuth::setToken($request->cookie('jwt_token'))->invalidate();
            } elseif ($request->bearerToken()) {
                JWTAuth::parseToken()->invalidate();
            }
        } catch (\Throwable $e) {
            // Token already expired or invalid
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Successfully logged out']);
        }

        return redirect()->route('products.index')
            ->withCookie(cookie()->forget('jwt_token'))
            ->with('success', 'You have been signed out.');
    }

    public function refresh(Request $request)
    {
        $newToken = JWTAuth::parseToken()->refresh();
        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ])->withCookie(cookie('jwt_token', $newToken, config('jwt.ttl'), '/', null, false, true));
    }

    protected function redirectByRole(User $user, string $token, string $message)
    {
        $cookie = cookie('jwt_token', $token, config('jwt.ttl', 1440), '/', null, false, true, false, 'Lax');

        $route = match ($user->role) {
            'admin' => 'admin.dashboard',
            'vendor' => 'vendor.dashboard',
            default => 'products.index',
        };

        return redirect()->route($route)->withCookie($cookie)->with('success', $message);
    }
}
