@extends('layouts.main')

@section('title', 'Race Kit Collection')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Race Week</p>
            <h2 class="text-3xl font-bold text-gray-900">Race kit collection</h2>
            <p class="text-sm text-gray-500 mt-1">Save your QR credential and follow your selected collection schedule.</p>
        </div>
        <span class="bg-white px-4 py-2 rounded-full shadow-sm text-sm font-semibold flex items-center gap-2 border border-gray-200">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Nusantara Trail Series
        </span>
    </div>

    <!-- Layout Dua Kolom (Kiri & Kanan) -->
    <div class="flex gap-6 pb-6">
        
        <!-- KOLOM KIRI -->
        <div class="flex-1 space-y-6">
            
            <!-- Box 1: Collection Slot Confirmed -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-base text-gray-900">Collection slot confirmed</h3>
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Scheduled</span>
                </div>
                <p class="text-xs text-gray-400 mb-4">Show your QR credential to the check-in staff.</p>

                <!-- 3 Kotak Informasi (Date, Time, Location) -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Date</p>
                        <p class="text-sm font-bold text-gray-800">Friday, 5 December 2026</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Time</p>
                        <p class="text-sm font-bold text-gray-800">10.00–18.00 WIB</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Location</p>
                        <p class="text-sm font-bold text-gray-800 truncate">Wisma Boga Solo Baru</p>
                    </div>
                </div>

                <!-- Lokasi Detail & Open Map -->
                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600">
                    <i class="ph ph-map-pin text-orange-500 text-base"></i>
                    <span>Sekipan Campground, Forest Area, Kalisoro, Tawangmangu District</span>
                    <a href="#" class="text-orange-500 font-bold hover:underline ml-1 flex items-center gap-0.5">
                        Open map <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>

            <!-- Box 2: 120K Race Kit Contents -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-base text-gray-900 mb-4">120K race kit contents</h3>
                <div class="grid grid-cols-5 gap-3">
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">BIB</p>
                        <p class="text-xs font-bold text-gray-800">M30-0184</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Jersey</p>
                        <p class="text-xs font-bold text-gray-800">Size L</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Timing chip</p>
                        <p class="text-xs font-bold text-gray-800">Attached</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Drop bag</p>
                        <p class="text-xs font-bold text-gray-800">3 tags</p>
                    </div>
                    <div class="bg-gray-50/80 border border-gray-100 p-3 rounded-xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Merch</p>
                        <p class="text-xs font-bold text-gray-800">Bandana</p>
                    </div>
                </div>
            </div>

            <!-- Box 3: What to Bring -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-base text-gray-900 mb-4">What to bring</h3>
                <div class="space-y-3">
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-[#2e7d32] text-xs font-bold flex items-center justify-center">1</span>
                            <span class="text-sm font-medium text-gray-800">Original ID matching your registration</span>
                        </div>
                        <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Ready</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-[#2e7d32] text-xs font-bold flex items-center justify-center">2</span>
                            <span class="text-sm font-medium text-gray-800">Mandatory gear for your race category</span>
                        </div>
                        <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Ready</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-[#2e7d32] text-xs font-bold flex items-center justify-center">3</span>
                            <span class="text-sm font-medium text-gray-800">QR race credential in the participant portal</span>
                        </div>
                        <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Ready</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold flex items-center justify-center">4</span>
                            <span class="text-sm font-medium text-gray-800">Authorization letter for proxy collection</span>
                        </div>
                        <span class="bg-[#fde9de] text-[#b45309] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Check</span>
                    </div>

                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (QR Code Card) -->
        <div class="w-[340px] space-y-4">
            
            <!-- Warning Box -->
            <div class="bg-[#fef3c7]/60 border border-[#f59e0b]/30 p-4 rounded-2xl">
                <h4 class="font-bold text-xs text-[#92400e] uppercase tracking-wider mb-1">Important note</h4>
                <p class="text-xs text-[#b45309] leading-relaxed">This QR code can only be used once. Do not share screenshots with others.</p>
            </div>

            <!-- QR Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center flex flex-col items-center">
                <p class="text-[10px] font-bold text-orange-500 uppercase tracking-widest mb-1">Bromo Tengger Trail</p>
                <h3 class="font-black text-2xl text-gray-900 tracking-tight">M30-0184</h3>
                <p class="text-xs text-gray-400 mt-0.5 mb-6">30K • Raka Aditya Pradipta</p>

                <!-- Gambar/Simulasi QR Code -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-4 inline-block">
                    <!-- Menggunakan gambar QR sample statis agar persis gambar asli -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=NTS26-RK-B30-0184" alt="QR Code" class="w-44 h-44 object-contain">
                </div>
                
                <p class="text-[10px] font-mono text-gray-400 mb-6">NTS26-RK-B30-0184</p>

                <!-- Tombol Download QR -->
                <button class="w-full bg-[#0c2016] hover:bg-[#183626] transition-colors text-white py-3 rounded-xl font-bold text-sm shadow-sm">
                    Download QR
                </button>
            </div>

        </div>

    </div>
@endsection