<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = Profile::where('user_id', (string) $request->user()->getKey())->first();

        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Profil belum dibuat'], 404);
        }

        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:120',
            'bibName' => 'required|string|max:30',
            'gender' => 'required|in:male,female',
            'dateOfBirth' => 'required|date|before:today',
            'bloodType' => 'nullable|in:A,B,AB,O',
            'nationality' => 'required|string|size:2',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postalCode' => 'nullable|string|max:10',
            'identity_type' => 'required|in:KTP,Passport,SIM',
            'identity_number' => 'required|string|max:50',
            'itraId' => 'nullable|string|max:50',
            'utmbId' => 'nullable|string|max:50',
            'emergency_name' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'emergency_relationship' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $data = $validator->validated();
        $data['nationality'] = strtoupper($data['nationality']);

        $profile = Profile::updateOrCreate(
            ['user_id' => (string) $request->user()->getKey()],
            $data
        );

        return response()->json(['success' => true, 'message' => 'Profil berhasil disimpan', 'data' => $profile]);
    }
}