@extends('layouts.main')

@section('title', 'Participant Profile')

@section('content')
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
                <!-- Avatar Placeholder -->
                <div class="w-16 h-16 bg-[#d9ebd4] rounded-full"></div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Raka Aditya Pradipta</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Male • Bandung • Indonesia</p>
                </div>
            </div>
            <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Profile 100%</span>
        </div>

        <!-- Race Credential -->
        <div class="bg-white p-6 rounded-2xl shadow-sm w-[300px] border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-sm text-gray-900">Race credential</h4>
                <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wide">Active</span>
            </div>
            <div class="flex justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Participant ID</p>
                    <p class="font-bold text-xl text-gray-900">NTS-260184</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">UTMB Index</p>
                    <p class="font-bold text-xl text-gray-900">512</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-lg text-gray-900">Personal information</h4>
            <button class="bg-black hover:bg-gray-800 transition-colors text-white px-5 py-2 rounded-lg text-sm font-bold">Edit Profile</button>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Full Name</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">Raka Aditya Pradipta</div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Identity Number</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">3273••••••••0184</div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date of Birth</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">14 February 1992</div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blood Type</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">O+</div>
            </div>
            <!-- Tambahan Email & Nomor Telepon -->
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">raka.pradipta@email.com</div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mobile Number</label>
                <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-lg mt-1 text-sm font-medium text-gray-800">+62 812 8841 0921</div>
            </div>
        </div>
    </div>

    <!-- Bottom Section (Address & Emergency Contact) -->
    <div class="flex gap-6 pb-6">
        <!-- Runner Address -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex-1 border border-gray-100">
            <h4 class="font-bold text-base text-gray-900 mb-4">Runner address</h4>
            <p class="text-sm text-gray-500 leading-relaxed pr-8">Jl. Ciumbuleuit No. 114, Hegarmanah, Cidadap, Bandung, West Java 40141</p>
        </div>
        
        <!-- Emergency Contact -->
        <div class="bg-white p-6 rounded-2xl shadow-sm flex-1 border border-gray-100">
            <h4 class="font-bold text-base text-gray-900 mb-4">Emergency contact</h4>
            <div class="flex items-start justify-between">
                <div>
                    <h5 class="font-bold text-sm text-gray-900">Maya Pradipta</h5>
                    <p class="text-sm text-gray-500 mt-1">Wife • +62 811 2045 778</p>
                </div>
                <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Verified</span>
            </div>
        </div>
    </div>
@endsection