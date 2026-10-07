@extends('layouts.admin')

@section('title', 'Race Overview')

@section('content')
<!-- Header Title & Export Button -->
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Race overview</h1>
        <p class="text-sm text-gray-500 mt-0.5">A clear view of your race, from registration to the start line.</p>
    </div>
    <button class="bg-white border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 flex items-center space-x-2">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Export report</span>
    </button>
</div>

<!-- Sub-banner Race Info -->
<div class="bg-white border border-emerald-100 rounded-xl p-4 mb-6 flex justify-between items-center shadow-sm">
    <div class="flex items-center space-x-3">
        <span class="text-emerald-600 font-bold bg-emerald-50 p-2 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </span>
        <div>
            <span class="font-semibold text-gray-900">Khao Yai Trail 2026</span>
            <span class="text-xs text-gray-500 ml-2">24 Oct 2026 · Pak Chong, Thailand</span>
        </div>
    </div>
    <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-3 py-1 rounded-full">Registration open</span>
</div>

<!-- Grid Statistik Utama (4 Kolom) & Banner Kanan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <!-- Kolom Kiri: 4 Kartu Statistik -->
    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm relative">
            <div class="text-xs text-gray-400 font-medium">Total registrations</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">584</div>
            <div class="text-xs text-gray-500 mt-2">+28 in the last 7 days</div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm relative">
            <div class="text-xs text-gray-400 font-medium">Revenue</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">฿1,842,000</div>
            <div class="text-xs text-gray-500 mt-2">561 paid · 23 awaiting payment</div>
        </div>
        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm relative">
            <div class="text-xs text-gray-400 font-medium">Slots remaining</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">116</div>
            <div class="text-xs text-gray-500 mt-2">83% of total capacity filled</div>
        </div>
        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm relative">
            <div class="text-xs text-gray-400 font-medium">Kits collected</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">312</div>
            <div class="text-xs text-gray-500 mt-2">53% of registered runners</div>
        </div>
    </div>

    <!-- Kolom Kanan: Notification Review Card -->
    <div class="bg-[#fcf8f0] border border-amber-200 rounded-xl p-5 flex flex-col justify-between shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-amber-600 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-bold text-gray-900 text-sm">5 qualification requests waiting for review</span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed">The oldest request was submitted 2 days ago. Keep runners moving toward the start line.</p>
        </div>
        <a href="#" class="inline-flex items-center text-sm font-semibold text-amber-800 hover:text-amber-900 mt-4">
            <span>Review qualifications</span>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>

</div>

<!-- Section: Category Quotas -->
<div class="bg-white border border-gray-200 rounded-xl p-6 mb-6 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-gray-900 text-base">Category quotas</h3>
        <span class="text-xs text-gray-400">584 / 700 slots</span>
    </div>
    
    <div class="space-y-4">
        <!-- Progress 1 -->
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-gray-800">20K · Forest Run</span>
                <span class="text-xs text-gray-500">246 / 300 · 54 left</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                <div class="bg-[#1b4332] h-full rounded-full" style="width: 82%"></div>
            </div>
        </div>
        <!-- Progress 2 -->
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-gray-800">50K · Ridge Trail</span>
                <span class="text-xs text-gray-500">218 / 250 · 32 left</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                <div class="bg-[#1b4332] h-full rounded-full" style="width: 87%"></div>
            </div>
        </div>
        <!-- Progress 3 -->
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-gray-800">100K · Ultra Trail</span>
                <span class="text-xs text-gray-500">120 / 150 · 30 left</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                <div class="bg-[#1b4332] h-full rounded-full" style="width: 80%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Section: Recent Registrations Table -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 flex justify-between items-center border-b border-gray-100">
        <h3 class="font-bold text-gray-900 text-base">Recent registrations</h3>
        <a href="#" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">View all participants →</a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-xs uppercase text-gray-400 border-b border-gray-100 bg-gray-50/50">
                    <th class="py-3 px-6 font-semibold">Participant</th>
                    <th class="py-3 px-6 font-semibold">BIB</th>
                    <th class="py-3 px-6 font-semibold">Category</th>
                    <th class="py-3 px-6 font-semibold">Registered</th>
                    <th class="py-3 px-6 font-semibold">Payment</th>
                    <th class="py-3 px-6 font-semibold">Kit status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <tr>
                    <td class="py-4 px-6">
                        <div class="font-medium text-gray-900">Maya Thompson</div>
                        <div class="text-xs text-gray-400">maya.thompson@email.com</div>
                    </td>
                    <td class="py-4 px-6 text-gray-600">5021</td>
                    <td class="py-4 px-6 text-gray-800 font-medium">50K · Ridge Trail</td>
                    <td class="py-4 px-6 text-gray-500">06 Oct, 14:32</td>
                    <td class="py-4 px-6"><span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded">Paid</span></td>
                    <td class="py-4 px-6"><span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded">Collected</span></td>
                </tr>
                <tr>
                    <td class="py-4 px-6">
                        <div class="font-medium text-gray-900">Niran Chaiyaporn</div>
                        <div class="text-xs text-gray-400">niran.c@email.com</div>
                    </td>
                    <td class="py-4 px-6 text-gray-600">2048</td>
                    <td class="py-4 px-6 text-gray-800 font-medium">20K · Forest Run</td>
                    <td class="py-4 px-6 text-gray-500">06 Oct, 13:18</td>
                    <td class="py-4 px-6"><span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded">Paid</span></td>
                    <td class="py-4 px-6 text-gray-400 text-xs">Not collected</td>
                </tr>
                <tr>
                    <td class="py-4 px-6">
                        <div class="font-medium text-gray-900">Arun Srisai</div>
                        <div class="text-xs text-gray-400">arun.s@email.com</div>
                    </td>
                    <td class="py-4 px-6 text-gray-600">1007</td>
                    <td class="py-4 px-6 text-gray-800 font-medium">100K · Ultra Trail</td>
                    <td class="py-4 px-6 text-gray-500">06 Oct, 11:54</td>
                    <td class="py-4 px-6"><span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded">Paid</span></td>
                    <td class="py-4 px-6 text-gray-400 text-xs">Not collected</td>
                </tr>
                <tr>
                    <td class="py-4 px-6">
                        <div class="font-medium text-gray-900">Sofia Martin</div>
                        <div class="text-xs text-gray-400">sofia.m@email.com</div>
                    </td>
                    <td class="py-4 px-6 text-gray-600">5032</td>
                    <td class="py-4 px-6 text-gray-800 font-medium">50K · Ridge Trail</td>
                    <td class="py-4 px-6 text-gray-500">05 Oct, 16:10</td>
                    <td class="py-4 px-6"><span class="bg-amber-50 text-amber-700 text-xs font-semibold px-2.5 py-1 rounded">Awaiting payment</span></td>
                    <td class="py-4 px-6 text-gray-400 text-xs">Not collected</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
        <div>Showing the 4 most recent registrations</div>
        <div class="flex items-center space-x-1">
            <span class="px-2 py-1 text-gray-400 cursor-pointer">&lt;</span>
            <span class="px-2.5 py-1 bg-gray-900 text-white font-medium rounded">1</span>
            <span class="px-2.5 py-1 hover:bg-gray-100 rounded cursor-pointer">2</span>
            <span class="px-2.5 py-1 hover:bg-gray-100 rounded cursor-pointer">3</span>
            <span class="px-2 py-1 text-gray-600 cursor-pointer">&gt;</span>
        </div>
    </div>
</div>
@endsection