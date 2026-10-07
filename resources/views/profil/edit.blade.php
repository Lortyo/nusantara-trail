@extends('layouts.main')

@section('title', 'Edit Participant Profile')

@section('content')
    <!-- Header & Navigation -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Participant Account</p>
            <h2 class="text-3xl font-bold text-gray-900">Edit participant profile</h2>
            <p class="text-sm text-gray-500 mt-1">Update your personal details, contact info, and runner credentials.</p>
        </div>
        <a href="{{ route('profil.show') }}" class="bg-white border border-gray-200 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
            ← Cancel & Back to Profile
        </a>
    </div>

    <!-- Error Validation Messages -->
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profil.update') }}" method="POST" class="space-y-6 pb-8">
        @csrf

        <!-- SECTION 1: PERSONAL INFORMATION -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-base text-gray-900 mb-4 pb-2 border-b border-gray-100">1. Personal Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Full Name (as in ID)</label>
                    <input type="text" name="fullName" value="{{ old('fullName', $profile->fullName ?? $user->name) }}" required 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">BIB Name (Name on Chest Number)</label>
                    <input type="text" name="bibName" value="{{ old('bibName', $profile->bibName ?? '') }}" required placeholder="e.g. RAKA" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Gender</label>
                    <select name="gender" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                        <option value="male" {{ old('gender', $profile->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $profile->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Date of Birth</label>
                    <input type="date" name="dateOfBirth" value="{{ old('dateOfBirth', $profile->dateOfBirth ?? '') }}" required 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Blood Type</label>
                    <select name="bloodType" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                        <option value="">-- Select Blood Type --</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                            <option value="{{ $bt }}" {{ old('bloodType', $profile->bloodType ?? '') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $profile->nationality ?? 'Indonesia') }}" required 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Identity Type</label>
                    <select name="identity_type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                        <option value="KTP" {{ old('identity_type', $profile->identity_type ?? '') == 'KTP' ? 'selected' : '' }}>KTP (National ID)</option>
                        <option value="Passport" {{ old('identity_type', $profile->identity_type ?? '') == 'Passport' ? 'selected' : '' }}>Passport</option>
                        <option value="SIM" {{ old('identity_type', $profile->identity_type ?? '') == 'SIM' ? 'selected' : '' }}>SIM (Driver's License)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Identity Number</label>
                    <input type="text" name="identity_number" value="{{ old('identity_number', $profile->identity_number ?? '') }}" required placeholder="e.g. 3273000000000001" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>
            </div>
        </div>

        <!-- SECTION 2: RUNNER ADDRESS & CONTACT -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-base text-gray-900 mb-4 pb-2 border-b border-gray-100">2. Address & Mobile Contact</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Mobile Number (WhatsApp)</label>
                    <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required placeholder="+62 812 3456 7890" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">City</label>
                    <input type="text" name="city" value="{{ old('city', $profile->city ?? '') }}" required placeholder="Bandung" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Country</label>
                    <input type="text" name="country" value="{{ old('country', $profile->country ?? 'Indonesia') }}" required 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Postal Code</label>
                    <input type="text" name="postalCode" value="{{ old('postalCode', $profile->postalCode ?? '') }}" placeholder="40141" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Full Address</label>
                    <textarea name="address" rows="2" required placeholder="Jl. Ciumbuleuit No. 114, Hegarmanah, Cidadap..." 
                              class="w-full border border-gray-200 rounded-xl p-3 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">{{ old('address', $profile->address ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 3: EMERGENCY CONTACT & RACE CREDENTIALS -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-base text-gray-900 mb-4 pb-2 border-b border-gray-100">3. Emergency Contact & Credentials</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Emergency Contact Name</label>
                    <input type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? '') }}" required placeholder="e.g. Maya Pradipta" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Emergency Phone Number</label>
                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" required placeholder="+62 811 2045 778" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">Relationship</label>
                    <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship', $profile->emergency_relationship ?? '') }}" required placeholder="Wife / Husband / Parent" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-50">
                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">ITRA ID <span class="text-gray-400 lowercase">(optional)</span></label>
                    <input type="text" name="itraId" value="{{ old('itraId', $profile->itraId ?? '') }}" placeholder="e.g. NTS-260184" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-900 mb-1.5 uppercase tracking-wider">UTMB Index <span class="text-gray-400 lowercase">(optional)</span></label>
                    <input type="text" name="utmbId" value="{{ old('utmbId', $profile->utmbId ?? '') }}" placeholder="e.g. 512" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016]">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('profil.show') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-6 py-3 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="bg-[#0c2016] hover:bg-[#183626] text-white font-bold text-xs px-8 py-3 rounded-xl transition shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
@endsection