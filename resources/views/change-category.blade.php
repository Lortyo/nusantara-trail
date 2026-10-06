@extends('layouts.main')

@section('title', 'My Events')

@section('content')
<!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-orange-500 text-[11px] font-bold uppercase mb-1 tracking-wider">Registration Management</p>
            <h2 class="text-3xl font-bold text-gray-900">Upgrade / downgrade category</h2>
            <p class="text-sm text-gray-500 mt-1">Choose a new category and review the price difference before confirming.</p>
        </div>
        <span class="bg-white px-4 py-2 rounded-full shadow-sm text-sm font-semibold flex items-center gap-2 border border-gray-200">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Bromo Tengger Trail 2026
        </span>
    </div>

    <!-- Alert Banner -->
    <div class="bg-[#fef3c7]/60 border border-[#f59e0b]/30 p-4 rounded-2xl mb-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <i class="ph ph-warning text-[#b45309] text-xl"></i>
            <p class="text-xs text-[#b45309] font-medium">Category changes close on 30 June 2026, 23:59 WIB. Subject to availability and eligibility.</p>
        </div>
        <span class="bg-[#fde68a] text-[#78350f] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">9 days left</span>
    </div>

    <!-- 3 Kartu Pilihan Kategori -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        
        <!-- Kartu 1: 15K (Downgrade) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-black text-3xl text-gray-900">15K</h3>
                        <p class="text-xs text-gray-400 font-medium">Rookie</p>
                    </div>
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Downgrade</span>
                </div>

                <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Elevation</span>
                        <span class="font-bold text-gray-800">+720 m</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Cut-off</span>
                        <span class="font-bold text-gray-800">5 hours</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Price</span>
                        <span class="font-bold text-gray-800">Rp 550.000</span>
                    </div>
                </div>
            </div>

            <button class="mt-6 w-full bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 rounded-xl font-bold text-sm transition-colors">
                Select downgrade
            </button>
        </div>

        <!-- Kartu 2: 30K (Active - Highlighted with dark green background) -->
        <div class="bg-[#0c2016] p-6 rounded-2xl shadow-md border border-[#153123] text-white flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-black text-3xl text-white">30K</h3>
                        <p class="text-xs text-gray-300 font-medium">Explorer</p>
                    </div>
                    <span class="bg-[#e4f5e8] text-[#2e7d32] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Active</span>
                </div>

                <div class="space-y-3 pt-4 border-t border-[#183626] text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Elevation</span>
                        <span class="font-bold text-white">+1.850 m</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Cut-off</span>
                        <span class="font-bold text-white">9 hours</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Price</span>
                        <span class="font-bold text-white">Rp 850.000</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 w-full bg-[#183626] text-white py-3 rounded-xl font-bold text-sm text-center border border-[#234834]">
                Current category
            </div>
        </div>

        <!-- Kartu 3: 60K (Upgrade) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-black text-3xl text-gray-900">60K</h3>
                        <p class="text-xs text-gray-400 font-medium">Ultra</p>
                    </div>
                    <span class="bg-[#fde9de] text-[#b45309] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">Upgrade</span>
                </div>

                <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Elevation</span>
                        <span class="font-bold text-gray-800">+3.900 m</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Cut-off</span>
                        <span class="font-bold text-gray-800">18 hours</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-xs">Price</span>
                        <span class="font-bold text-gray-800">Rp 1.450.000</span>
                    </div>
                </div>
            </div>

            <button class="mt-6 w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold text-sm transition-colors shadow-sm">
                Select upgrade
            </button>
        </div>

    </div>

    <!-- Bagian Bawah: Ringkasan & Persyaratan -->
    <div class="flex gap-6 pb-6">
        
        <!-- Kolom Kiri: Change Summary -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex-1">
            <div class="flex justify-between items-center mb-5">
                <h4 class="font-bold text-base text-gray-900">Change summary</h4>
                <span class="bg-[#fde9de] text-[#b45309] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">60K Preview</span>
            </div>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Current category</span>
                    <span class="font-bold text-gray-900">30K Explorer</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">New category</span>
                    <span class="font-bold text-gray-900">60K Ultra</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Price difference</span>
                    <span class="font-bold text-gray-900">Rp 600.000</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Administration fee</span>
                    <span class="font-bold text-gray-900">Rp 25.000</span>
                </div>
                <div class="flex justify-between py-3 text-base">
                    <span class="font-bold text-gray-900">Total payment</span>
                    <span class="font-black text-orange-500 text-xl">Rp 625.000</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: 60K Upgrade Requirements -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 w-[400px] flex flex-col justify-between">
            <div>
                <h4 class="font-bold text-base text-gray-900 mb-5">60K upgrade requirements</h4>
                
                <div class="space-y-4">
                    <!-- Syarat 1 -->
                    <div class="flex items-start gap-3">
                        <i class="ph ph-check-circle text-green-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-900">ITRA Performance Index ≥ 450</p>
                            <p class="text-xs text-gray-500 mt-0.5">512 • <span class="text-green-600 font-semibold">Eligible</span></p>
                        </div>
                    </div>

                    <!-- Syarat 2 -->
                    <div class="flex items-start gap-3">
                        <i class="ph ph-check-circle text-green-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Trail finish ≥ 30K / 12 months</p>
                            <p class="text-xs text-gray-500 mt-0.5">Rinjani 30K • <span class="text-green-600 font-semibold">Eligible</span></p>
                        </div>
                    </div>

                    <!-- Syarat 3 -->
                    <div class="flex items-start gap-3">
                        <i class="ph ph-warning-circle text-orange-500 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Medical certificate</p>
                            <p class="text-xs text-orange-500 font-medium mt-0.5">Not uploaded</p>
                        </div>
                    </div>
                </div>
            </div>

            <button class="mt-6 w-full bg-[#0c2016] hover:bg-[#183626] transition-colors text-white py-3 rounded-xl font-bold text-sm shadow-sm flex items-center justify-center gap-2">
                <span>Upload document & continue</span>
                <i class="ph ph-arrow-right font-bold"></i>
            </button>
        </div>

    </div>


@endsection