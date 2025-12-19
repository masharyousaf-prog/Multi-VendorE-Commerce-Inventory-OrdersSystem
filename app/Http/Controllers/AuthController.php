<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Notifications
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{
    // --- LOGIN ---

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt Login
        if (Auth::attempt($credentials)) 
        {
            $user = Auth::user();

            // --- BLOCK USERS WHO ARE NOT APPROVED ---
            if ($user->role !== 'admin' && $user->is_active == 0) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is pending approval by Admin.'
                ]);
            }

            $request->session()->regenerate();

            // Redirect based on role
            if ($user->role === 'admin')   return redirect()->route('admin.dashboard');
            if ($user->role === 'vendor')  return redirect()->route('vendor.dashboard');

            return redirect('/');
        }

        // 3. Failed Login
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // --- REGISTER ---

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validation
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:customer,vendor'
        ]);

        // Create User
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'is_active'=> 0, // NEW users must be approved first
        ]);

        // --- NEW FEATURE: Notify Admins ---
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewUserRegistered($user));

        // Log the user in
        //Auth::login($user);

        // Redirect Based on Role
       // if ($user->role === 'vendor') return redirect()->route('vendor.dashboard');
return redirect()->route('login')->with('success', 'Account created. Please login.');
       // return redirect('/');
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
