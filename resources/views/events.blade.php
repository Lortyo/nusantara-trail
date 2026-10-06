@extends('layouts.main')

@section('title', 'Find Trail Events')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Find Events</p>
            <h2 class="text-3xl font-bold text-gray-900">Find trail events</h2>
            <p class="text-sm text-gray-500 mt-1">Discover upcoming events and track your registration status.</p>
        </div>
        <span class="bg-white px-4 py-2 rounded-full shadow-sm text-sm font-semibold flex items-center gap-2 border border-gray-200">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Nusantara Trail Series
        </span>
    </div>

    <!-- Search Box Section -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <div class="flex justify-between items-center mb-3">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Find Events</span>
            <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">6 Upcoming Events</span>
        </div>
        <div class="relative">
            <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-gray-400 text-lg"></i>
            <input type="text" placeholder="Search event name..." class="w-full bg-gray-50/80 border border-gray-200 rounded-xl py-3 pl-11 pr-4 text-sm font-medium text-gray-800 focus:outline-none focus:border-gray-400">
        </div>
    </div>

    <!-- Section Title: Upcoming Events -->
    <div class="mb-4">
        <span class="text-[10px] font-bold text-orange-500 uppercase tracking-wider">Race Calendar</span>
        <h3 class="text-xl font-bold text-gray-900">Upcoming events</h3>
        <p class="text-xs text-gray-400">6 events found for the 2026 season</p>
    </div>

    <!-- Grid Kartu Events (Dengan Gambar Banner) -->
    <div class="grid grid-cols-3 gap-6 mb-8">
        
        <!-- Event 1: Bromo 100 2026 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <!-- Banner Image -->
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=600&q=80" alt="Bromo" class="w-full h-full object-cover">
                    <!-- Badge Tanggal (Kiri Bawah Gambar) -->
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        12 JUL 2026
                    </div>
                    <!-- Badge Status (Kanan Atas Gambar) -->
                    <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        178 spots left
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Bromo 100 2026</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Cemoro Lawang, East Java
                    </p>

                    <!-- Kategori Badge -->
                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">35K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">50K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">100K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp395.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-[#0c2016] hover:bg-[#183626] text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors">Register</button>
                </div>
            </div>
        </div>

        <!-- Event 2: Merbabu 100 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80" alt="Merbabu" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        09 AUG 2026
                    </div>
                    <div class="absolute top-3 right-3 bg-[#2e7d32] text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        Registration open
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Merbabu 100</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Selo, Boyolali
                    </p>

                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">15K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">30K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp325.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-[#0c2016] hover:bg-[#183626] text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors">Register</button>
                </div>
            </div>
        </div>

        <!-- Event 3: Ijen Blue 100 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80" alt="Ijen" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        06 SEP 2026
                    </div>
                    <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        64 spots left
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Ijen Blue 100</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Licin, Banyuwangi
                    </p>

                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">30K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">60K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp445.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-[#0c2016] hover:bg-[#183626] text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors">Register</button>
                </div>
            </div>
        </div>

        <!-- Event 4: Bandung 100 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=600&q=80" alt="Bandung" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        04 OCT 2026
                    </div>
                    <div class="absolute top-3 right-3 bg-[#2e7d32] text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        Registration open
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Bandung 100</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Cisurupan, Garut
                    </p>

                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">15K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">30K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp285.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-[#0c2016] hover:bg-[#183626] text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors">Register</button>
                </div>
            </div>
        </div>

        <!-- Event 5: Lawu Ultra 100 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=600&q=80" alt="Lawu" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        25 OCT 2026
                    </div>
                    <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        Waitlist
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Lawu Ultra 100</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Tawangmangu, Karanganyar
                    </p>

                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">30K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">60K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp475.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl text-xs font-bold transition-colors border border-gray-200">Join waitlist</button>
                </div>
            </div>
        </div>

        <!-- Event 6: Agung 100 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1542332213-9b5a5a3fad35?auto=format&fit=crop&w=600&q=80" alt="Agung" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        15 NOV 2026
                    </div>
                    <div class="absolute top-3 right-3 bg-blue-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                        Opening soon
                    </div>
                </div>

                <div class="p-6 pb-2">
                    <h4 class="font-black text-xl text-gray-900 mb-1">Agung 100</h4>
                    <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                        <i class="ph ph-map-pin text-orange-500"></i> Kintamani, Bali
                    </p>

                    <div class="flex gap-2 mb-4">
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">15K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">30K</span>
                        <span class="bg-gray-50 border border-gray-200 text-gray-700 text-[11px] font-bold px-3 py-1 rounded-lg">60K</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold">From</p>
                    <p class="font-bold text-base text-gray-900">Rp425.000</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#" class="text-xs font-bold text-gray-600 hover:underline">View details</a>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl text-xs font-bold transition-colors border border-gray-200">Notify me</button>
                </div>
            </div>
        </div>

    </div>

    <!-- Pagination Footer -->
    <div class="flex justify-between items-center pb-6 text-sm text-gray-500">
        <p>Showing 1–6 of 6 events</p>
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 font-bold text-gray-400 cursor-not-allowed text-xs">Previous</button>
            <button class="w-8 h-8 rounded-xl bg-[#0c2016] text-white font-bold text-xs flex items-center justify-center">1</button>
            <button class="w-8 h-8 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-xs flex items-center justify-center">2</button>
            <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 font-bold text-gray-700 text-xs flex items-center gap-1">
                Next <i class="ph ph-arrow-right"></i>
            </button>
        </div>
    </div>
@endsection