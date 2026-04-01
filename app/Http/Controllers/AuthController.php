<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function showWelcome()
    {
        return view('auth.welcome');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $email = strtolower($data['email']);
    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Account not found. Please sign up.'])->withInput();
    }

    if (!Hash::check($data['password'], $user->password)) {
        return back()->withErrors(['password' => 'Invalid password.'])->withInput();
    }

    // Use the user’s name from DB as display name
    $derivedName = $user->name;

    // Set session to mark user as logged in
    session([
        'auth.user' => [
            'name' => $derivedName,
            'role' => $user->role ?? 'student',
            'avatar' => $user->avatar ?? '/profile.png',
            'email' => $user->email,
        ],
    ]);

    // Clear guest enrolled classes
    session()->forget('classrooms.enrolled');

    return redirect()->route('dashboard');
}


    public function logout()
    {
        session()->forget('auth.user');
        return redirect()->route('welcome');
    }

    public function showSignupForm(string $role = 'student')
    {
        // This method is for initial display or when explicitly navigating to signup/{role}
        // The old('role') check in the blade template will handle re-display after errors.
        $role = strtolower($role) === 'teacher' ? 'teacher' : 'student';
        return view('auth.signup', compact('role'));
    }

    public function signup(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:teacher,student',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'required|string',
        ]);

        $email = strtolower($data['email']);
        if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return back()->withErrors(['email' => 'Email already registered. Please login.'])->withInput();
        }

        // Name is required; use provided display name
        $name = trim((string)($data['name'] ?? ''));
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'role' => $data['role'],
            'avatar' => '/profile.png',
            'password' => Hash::make($data['password']),
        ]);

        // Auto-login after signup
        session([
            'auth.user' => [
                'name' => $name,
                'role' => $user->role,
                'avatar' => $user->avatar ?? '/profile.png',
                'email' => $user->email,
            ],
        ]);

        // Ensure fresh start for enrolled classes
        session()->forget('classrooms.enrolled');

        return redirect()->route('dashboard');
    }
}