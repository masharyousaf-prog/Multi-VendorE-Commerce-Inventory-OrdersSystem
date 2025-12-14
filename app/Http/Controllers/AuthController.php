<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // --- LOGIN ---

    // Show the login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process the login form
    public function login(Request $request)
    {
        // 1. Validate Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt to log the user in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on role
            $user = Auth::user();
            if ($user->role === 'admin') return redirect()->route('admin.dashboard');
            if ($user->role === 'vendor') return redirect()->route('vendor.dashboard');

            return redirect()->intended('/');
        }

        // 3. If failed, go back with error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // --- REGISTER ---

    // Show the registration form
    public function showRegister()
    {
        return view('auth.register');
    }

    // Process the registration
    public function register(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed', // expects password_confirmation field
            'role' => 'required|in:customer,vendor'
        ]);

        // 2. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 3. Log them in immediately
        Auth::login($user);

        // 4. Redirect
        if ($user->role === 'vendor') return redirect()->route('vendor.dashboard');
        return redirect('/');
    }

    // --- LOGOUT ---

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
