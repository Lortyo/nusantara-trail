@extends('layouts.main')

@section('title', 'Participant Profile')

@section('content')
    <!-- Alert Notifikasi Berhasil -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Alert Jika Profil Belum Diisi sama sekali -->
    @if(!$profile)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-2xl flex justify-between items-center">
            <div>
                <p class="font-bold">Profil Anda belum lengkap!</p>
                <p class="text-xs text-amber-700 mt-0.5">Silakan isi data diri Anda terlebih dahulu sebelum melakukan pendaftaran event.</p>
            </div>
            <a href="{{ route('profil.edit') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition">
                Isi Profil Sekarang
            </a>
        </div>
    @endif

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Participant Account</p>
            <h2 class="text-3xl font-bold text-gray-900">Participant profile</h2>
            <p class="text-sm text-gray-500 mt-1">Make sure your identity and contact details are up to date before race day.</p>
        </div>
        <span class="bg-white px-4 py-2 rounded-full shadow-sm text-sm font-semibold flex items-center gap-2 border border-gray-200">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Nusantara Trail Series
        </span>
    </div>

    <!-- Top Cards (Profile & Race Credential) -->
    <div class="flex gap-6 mb-6">
        <!-- Main Profile Info -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex-1 flex items-center justify-between border border-gray-100">
            <div class="flex items-center gap-4">
                <!-- Avatar Initial Placeholder Dinamis -->
                <div class="w-16 h-16 bg-[#d9ebd4] rounded-full flex items-center justify-center font-bold text-xl text-[#2e7d32]">
                    {{ strtoupper(substr($profile->fullName ?? $user->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">{{ $profile->fullName ?? $user->name }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ ucfirst($profile->gender ?? '-') }} • {{ $profile->city ?? '-' }} • {{ $profile->country ?? '-' }}
                    </p>
                </div>
            </div>
            <span class="{{ $profile ? 'bg-[#e4f5e8] text-[#2e7d32]' : 'bg-gray-100 text-gray-500' }} text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">
                Profile {{ $profile ? '100%' : 'Incomplete' }}
            </span>
        </div>

        <!-- Race Credential -->
        <div class="bg-white p-6 rounded-2xl shadow-sm w-[300px] border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-sm text-gray-900">Race credential</h4>
                <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wide">
                    {{ $profile ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="flex justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">ITRA ID</p>
                    <p class="font-bold text-xl text-gray-900">{{ $profile->itraId ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">UTMB Index</p>
                    <p class="font-bold text-xl text-gray-900">{{ $profile->utmbId ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-lg text-gray-900">Personal information</h4>
            <a href="{{ route('profil.edit') }}" class="bg-black hover:bg-gray-800 transition-colors text-white px-5 py-2 rounded-lg text-sm font-bold inline-block">
                {{ $profile ? 'Edit Profile' : 'Fill Profile' }}
            </a>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Full Name</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ $profile->fullName ?? '-' }}
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    Identity Number {{ isset($profile->identity_type) ? '('.$profile->identity_type.')' : '' }}
                </label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ $profile->identity_number ?? '-' }}
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date of Birth</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ isset($profile->dateOfBirth) ? \Carbon\Carbon::parse($profile->dateOfBirth)->format('d F Y') : '-' }}
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blood Type</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ $profile->bloodType ?? '-' }}
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ $user->email }}
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mobile Number</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">
                    {{ $profile->phone ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section (Address & Emergency Contact) -->
    <div class="flex gap-6 pb-6">
        <!-- Runner Address -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex-1 border border-gray-100">
            <h4 class="font-bold text-base text-gray-900 mb-4">Runner address</h4>
            <p class="text-sm leading-relaxed pr-8 {{ isset($profile->address) ? 'text-gray-500' : 'text-gray-400 italic' }}">
                @if(isset($profile->address))
                    {{ $profile->address }}, {{ $profile->city }}, {{ $profile->country }} {{ $profile->postalCode }}
                @else
                    Belum ada data alamat yang diisi.
                @endif
            </p>
        </div>
        
        <!-- Emergency Contact -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex-1 border border-gray-100">
            <h4 class="font-bold text-base text-gray-900 mb-4">Emergency contact</h4>
            <div class="flex items-start justify-between">
                <div>
                    <h5 class="font-bold text-sm text-gray-900">{{ $profile->emergency_name ?? '-' }}</h5>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $profile->emergency_relationship ?? '-' }} • {{ $profile->emergency_phone ?? '-' }}
                    </p>
                </div>
                @if(isset($profile->emergency_name))
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Verified</span>
                @endif
            </div>
        </div>
    </div>
@endsection