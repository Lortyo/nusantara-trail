<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:60',
            'email' => 'required|email',
            'phone_code' => 'required|in:+62',
            'phone' => 'required|string|max:20',
            'nationality' => 'required|string|size:2',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $email = strtolower($data['email']);

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar'])->withInput();
        }

        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);

        $user = User::create([
            'name' => $fullName,
            'email' => $email,
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Profile::create([
            'user_id' => (string) $user->getKey(),
            'fullName' => $fullName,
            'bibName' => strtoupper($data['first_name']),
            'gender' => $data['gender'],
            'dateOfBirth' => $data['date_of_birth'],
            'nationality' => $data['nationality'],
            'phone' => $data['phone_code'] . ltrim(preg_replace('/\D/', '', $data['phone']), '0'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/events');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $credentials['email'] = strtolower($credentials['email']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/events');
        }

        return back()->withErrors(['email' => 'Email atau password salah'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}