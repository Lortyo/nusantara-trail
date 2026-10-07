<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // 1. Halaman Tampil Profil (Hanya Lihat Data)
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;

        return view('profil.show', compact('user', 'profile'));
    }

    // 2. Halaman Form Edit Profil
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile ?? new Profile();

        return view('profil.edit', compact('user', 'profile'));
    }

    // 3. Proses Simpan / Update Data
    public function update(Request $request)
    {
        $data = $request->validate([
            'fullName' => 'required|string|max:120',
            'bibName' => 'required|string|max:30',
            'gender' => 'required|in:male,female',
            'dateOfBirth' => 'required|date|before:today',
            'bloodType' => 'nullable|string|max:5',
            'nationality' => 'nullable|string|max:50',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postalCode' => 'nullable|string|max:10',
            'identity_type' => 'required|string|in:KTP,Passport,SIM',
            'identity_number' => 'required|string|max:50',
            'itraId' => 'nullable|string|max:50',
            'utmbId' => 'nullable|string|max:50',
            'emergency_name' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'emergency_relationship' => 'required|string|max:50',
        ]);

        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        return redirect()->route('profil.show')->with('success', 'Profil pelari berhasil diperbarui!');
    }
}