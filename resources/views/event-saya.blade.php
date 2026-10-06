@extends('layouts.main')

@section('title', 'My Events')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Registration</p>
            <h2 class="text-3xl font-bold text-gray-900">My events & registrations</h2>
            <p class="text-sm text-gray-500 mt-1">Track your registrations, payments, and upcoming events.</p>
        </div>
        <span class="bg-white px-4 py-2 rounded-full shadow-sm text-sm font-semibold flex items-center gap-2 border border-gray-200">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Nusantara Trail Series
        </span>
    </div>

    <!-- Tabel Races -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-lg text-gray-900">Races</h3>
        </div>
        
        <div class="p-6 pt-2">
            <!-- Baris 1: Lawu Ultra -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                <div class="w-1/4 font-bold text-sm text-gray-900">Lawu Ultra 100 2026</div>
                <div class="w-1/6 font-bold text-sm text-[#183626] text-center">120K</div>
                <div class="w-1/6 flex justify-center">
                    <span class="bg-[#fde9de] text-[#b45309] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Paid</span>
                </div>
                <div class="w-1/6 text-sm text-gray-400 text-center">00:00:00</div>
                <div class="w-1/6 text-right">
                    <a href="#" class="text-sm font-bold text-[#183626] hover:underline flex items-center justify-end gap-1.5">
                        View results <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>

            <!-- Baris 2: Agung -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                <div class="w-1/4 font-bold text-sm text-gray-900">Agung 100 2026</div>
                <div class="w-1/6 font-bold text-sm text-[#183626] text-center">70K</div>
                <div class="w-1/6 flex justify-center">
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Completed</span>
                </div>
                <div class="w-1/6 text-sm text-gray-400 text-center">18:48:12</div>
                <div class="w-1/6 text-right">
                    <a href="#" class="text-sm font-bold text-[#183626] hover:underline flex items-center justify-end gap-1.5">
                        View results <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>

            <!-- Baris 3: Rinjani -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                <div class="w-1/4 font-bold text-sm text-gray-900">Rinjani 100 2025</div>
                <div class="w-1/6 font-bold text-sm text-[#183626] text-center">30K</div>
                <div class="w-1/6 flex justify-center">
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Completed</span>
                </div>
                <div class="w-1/6 text-sm text-gray-400 text-center">05:18:42</div>
                <div class="w-1/6 text-right">
                    <a href="#" class="text-sm font-bold text-[#183626] hover:underline flex items-center justify-end gap-1.5">
                        View results <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>

            <!-- Baris 4: Merbabu -->
            <div class="flex items-center justify-between py-4">
                <div class="w-1/4 font-bold text-sm text-gray-900">Merbabu 100 2025</div>
                <div class="w-1/6 font-bold text-sm text-[#183626] text-center">15K</div>
                <div class="w-1/6 flex justify-center">
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Completed</span>
                </div>
                <div class="w-1/6 text-sm text-gray-400 text-center">02:07:11</div>
                <div class="w-1/6 text-right">
                    <a href="#" class="text-sm font-bold text-[#183626] hover:underline flex items-center justify-end gap-1.5">
                        View results <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection