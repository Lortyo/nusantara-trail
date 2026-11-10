@extends('layouts.admin')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $statusLabel = ['open' => 'Registration open', 'draft' => 'Draft', 'closed' => 'Registration closed', 'finished' => 'Completed'];
@endphp

<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-[34px] font-semibold tracking-tight text-forest-900">Race overview</h1>
        <p class="text-sm text-gray-500 mt-1">A clear view of your race, from registration to the start line.</p>
    </div>
    @if($event)
        <a href="/admin/dashboard/export?event={{ $event->getKey() }}" class="btn-outline"><i class="ph ph-download-simple"></i> Export report</a>
    @endif
</div>

@if(! $event)
    <div class="card p-10 mt-6 text-center text-sm text-gray-500">
        No events yet. <a href="{{ route('admin.events.create') }}" class="font-semibold text-forest-700 underline">Create your first event</a>
    </div>
@else
    <!-- Event aktif -->
    <div class="mt-6 rounded-2xl border border-[#cfe3d6] bg-[#eaf3ed] px-5 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3 flex-wrap">
            <i class="ph ph-mountains text-2xl text-forest-700"></i>
            <form method="GET" action="/admin/dashboard">
                <select name="event" onchange="this.form.submit()" class="bg-transparent font-semibold pr-6 focus:outline-none cursor-pointer">
                    @foreach($events as $e)
                        <option value="{{ $e->getKey() }}" @selected((string) $e->getKey() === (string) $event->getKey())>{{ $e->name }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-sm text-gray-500">
                {{ optional($event->eventDate)->format('d M Y') }} · {{ collect([data_get($event, 'location.city'), data_get($event, 'location.province')])->filter()->implode(', ') }}
            </span>
        </div>
        <span class="text-sm font-semibold text-forest-700">{{ $statusLabel[$event->status] ?? '' }}</span>
    </div>

    <!-- Kartu statistik -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
        <div class="card p-6">
            <div class="flex justify-between text-gray-500 text-sm"><span>Total registrations</span><i class="ph ph-users text-xl"></i></div>
            <p class="text-[42px] font-semibold mt-3 leading-none">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 mt-4">+{{ $stats['week'] }} in the last 7 days</p>
        </div>
        <div class="card p-6">
            <div class="flex justify-between text-gray-500 text-sm"><span>Revenue</span><i class="ph ph-wallet text-xl"></i></div>
            <p class="text-[34px] font-semibold mt-3 leading-none">{{ $rp($stats['revenue']) }}</p>
            <p class="text-sm text-gray-500 mt-4">{{ $stats['paid'] }} paid · {{ $stats['pending'] }} awaiting payment</p>
        </div>
        <div class="card p-6">
            <div class="flex justify-between text-gray-500 text-sm"><span>Slots remaining</span><i class="ph ph-flag text-xl"></i></div>
            <p class="text-[42px] font-semibold mt-3 leading-none">{{ $stats['left'] }}</p>
            <p class="text-sm text-gray-500 mt-4">{{ $stats['filledPct'] }}% of total capacity filled</p>
        </div>
        <div class="card p-6">
            <div class="flex justify-between text-gray-500 text-sm"><span>Kits collected</span><i class="ph ph-package text-xl"></i></div>
            <p class="text-[42px] font-semibold mt-3 leading-none">{{ $stats['kits'] }}</p>
            <p class="text-sm text-gray-500 mt-4">{{ $stats['kitsPct'] }}% of registered runners</p>
        </div>
    </div>

    <!-- Kuota + kualifikasi -->
    <div class="grid xl:grid-cols-[1.4fr_1fr] gap-5 mt-6">
        <div class="card p-7">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Category quotas</h2>
                <span class="text-sm text-gray-500">{{ $stats['quota'] - $stats['left'] }} / {{ $stats['quota'] }} slots</span>
            </div>
            <div class="mt-6 space-y-6">
                @forelse($catRows as $c)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold">{{ $c['label'] }}</span>
                            <span class="text-gray-500">{{ $c['taken'] }} / {{ $c['quota'] }} · {{ $c['left'] }} left</span>
                        </div>
                        <div class="h-2 rounded-full bg-[#e8efe9] mt-2"><div class="h-2 rounded-full bg-forest-700" style="width: {{ $c['pct'] }}%"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No categories yet for this event.</p>
                @endforelse
            </div>
        </div>

        @if($qual['count'] > 0)
            <div class="rounded-2xl border border-[#f1dfb8] bg-[#fbf0dc] p-7">
                <i class="ph ph-seal-warning text-3xl text-[#a8670f]"></i>
                <h2 class="text-[26px] font-semibold leading-tight mt-3">{{ $qual['count'] }} qualification {{ $qual['count'] === 1 ? 'request' : 'requests' }} waiting for review</h2>
                <p class="text-sm text-[#a8670f] mt-3">The oldest request was submitted {{ $qual['oldestDays'] }} {{ $qual['oldestDays'] === 1 ? 'day' : 'days' }} ago. Keep runners moving toward the start line.</p>
                <a href="/admin/qualifications" class="btn-outline mt-5"><i class="ph ph-arrow-right"></i> Review qualifications</a>
            </div>
        @else
            <div class="card p-7">
                <i class="ph ph-seal-check text-3xl text-forest-700"></i>
                <h2 class="text-xl font-semibold mt-3">No qualification requests waiting</h2>
                <p class="text-sm text-gray-500 mt-2">New requests from runners will appear here for review.</p>
            </div>
        @endif
    </div>

    <!-- Pendaftaran terbaru -->
    <div class="card mt-6">
        <div class="flex items-center justify-between p-7 pb-5">
            <h2 class="text-xl font-semibold">Recent registrations</h2>
            <a href="/admin/participants" class="text-sm font-semibold text-forest-700 hover:underline">View all participants →</a>
        </div>
        <table class="w-full">
            <thead class="bg-[#f8f7f2]">
                <tr><th class="th">Participant</th><th class="th">BIB</th><th class="th">Category</th><th class="th">Registered</th><th class="th">Payment</th><th class="th">Kit status</th></tr>
            </thead>
            <tbody class="divide-y divide-[#eceae2]">
                @forelse($rows as $r)
                    <tr>
                        <td class="td"><p class="font-semibold">{{ $r['name'] }}</p><p class="text-xs text-gray-500">{{ $r['email'] }}</p></td>
                        <td class="td">{{ $r['bib'] }}</td>
                        <td class="td">{{ $r['category'] }}</td>
                        <td class="td">{{ $r['registered'] }}</td>
                        <td class="td"><span class="chip {{ $r['paid'] ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#fdf0d5] text-[#a8670f]' }}">{{ $r['paid'] ? 'Paid' : 'Awaiting payment' }}</span></td>
                        <td class="td"><span class="chip {{ $r['kit'] ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#f0efea] text-gray-500' }}">{{ $r['kit'] ? 'Collected' : 'Not collected' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">No registrations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="flex items-center justify-between px-7 py-4 border-t border-[#eceae2] text-sm text-gray-500">
            <span>Showing the {{ $recent->count() }} most recent registrations</span>
            @if($recent->hasPages())
                <div class="flex items-center gap-3">
                    @if(! $recent->onFirstPage())<a href="{{ $recent->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>@endif
                    @foreach($recent->getUrlRange(1, $recent->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="{{ $page == $recent->currentPage() ? 'font-bold text-gray-900' : '' }}">{{ $page }}</a>
                    @endforeach
                    @if($recent->hasMorePages())<a href="{{ $recent->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>@endif
                </div>
            @endif
        </div>
    </div>
@endif
@endsection