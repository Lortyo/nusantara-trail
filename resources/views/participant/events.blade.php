@extends('layouts.participant')
@section('title', 'Events')
@section('eyebrow', 'Find events')
@section('heading', 'Find trail events')
@section('subtitle', 'Discover upcoming events and track your registration status.')

@section('content')
<form method="GET" action="/events" class="pcard p-7">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[11px] uppercase text-ember">Find events</p>
            <h2 class="text-2xl mt-1">Search by event name</h2>
        </div>
        <span class="chip bg-[#e3f1e7] text-[#1d6b3b] uppercase">{{ $events->total() }} upcoming events</span>
    </div>
    <div class="relative mt-5">
        <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-xl text-gray-500"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search event name..."
               class="w-full h-12 rounded-xl border border-gray-200 pl-12 pr-4 focus:outline-none focus:border-ink">
    </div>
</form>

<div class="mt-10">
    <p class="text-[13px] uppercase text-ember">Race calendar</p>
    <h2 class="text-[32px] font-medium">Upcoming events</h2>
    <p class="text-gray-500">{{ $events->total() }} events found for the {{ now()->year }} season</p>
</div>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-7 mt-6">
    @forelse($cards as $c)
        @php
            $badge = [
                'open' => ['REGISTRATION OPEN', 'bg-[#e3f1e7] text-[#1d6b3b]'],
                'spots_left' => [$c['left'] . ' SPOTS LEFT', 'bg-[#fdeee3] text-[#d9541c]'],
                'waitlist' => ['WAITLIST', 'bg-white/90 text-gray-600'],
                'opening_soon' => ['OPENING SOON', 'bg-white/90 text-gray-600'],
                'closed' => ['REGISTRATION CLOSED', 'bg-white/90 text-gray-600'],
            ][$c['state']];
        @endphp
        <div class="pcard overflow-hidden flex flex-col">
            <div class="relative h-[240px] bg-gradient-to-br from-leaf to-ink">
                @if($c['banner'])<img src="{{ $c['banner'] }}" alt="" class="absolute inset-0 w-full h-full object-cover">@endif
                <span class="chip absolute top-4 left-4 {{ $badge[1] }}">{{ $badge[0] }}</span>
                <span class="absolute bottom-4 left-4 rounded-lg bg-white/90 px-3 py-1.5 text-sm font-semibold uppercase">{{ $c['date'] }}</span>
            </div>
            <div class="p-6 flex flex-col flex-1">
                <h3 class="text-2xl">{{ $c['name'] }}</h3>
                <p class="mt-2 flex items-center gap-2 text-gray-500"><i class="ph-fill ph-map-pin text-ember"></i>{{ $c['location'] }}</p>
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($c['chips'] as $chip)<span class="chip bg-[#eeeee9] text-gray-600">{{ $chip }}</span>@endforeach
                </div>
                <div class="mt-auto pt-6 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500">From</p>
                        <p class="text-xl font-semibold">{{ $c['from'] ? 'Rp' . number_format($c['from'], 0, ',', '.') : '-' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="/events/{{ $c['id'] }}" class="text-sm font-semibold text-leaf hover:underline">View details</a>

                        @if(in_array($c['state'], ['open', 'spots_left']))
                            <a href="/events/{{ $c['id'] }}#categories" class="btn-dark">Register</a>
                        @elseif($c['state'] === 'closed')
                            <span class="btn-ghost opacity-60">Closed</span>
                        @else
                            @php $type = $c['state'] === 'waitlist' ? 'waitlist' : 'notify'; @endphp
                            @if(in_array($type, $c['subs']))
                                <span class="btn-ghost opacity-60">{{ $type === 'waitlist' ? 'On waitlist' : 'We will notify you' }}</span>
                            @else
                                <form method="POST" action="/events/{{ $c['id'] }}/subscribe">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <button type="submit" class="btn-ghost">{{ $type === 'waitlist' ? 'Join waitlist' : 'Notify me' }}</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="pcard p-10 text-gray-500 md:col-span-2 xl:col-span-3">No upcoming events found.</div>
    @endforelse
</div>

<div class="flex items-center justify-between mt-10 text-sm text-gray-500">
    <span>Showing {{ $events->firstItem() ?? 0 }}–{{ $events->lastItem() ?? 0 }} of {{ $events->total() }} events</span>
    <div class="flex items-center gap-2">
        @if($events->onFirstPage())<span class="btn-ghost opacity-50">Previous</span>@else<a href="{{ $events->previousPageUrl() }}" class="btn-ghost">Previous</a>@endif
        @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="w-11 h-11 rounded-xl flex items-center justify-center font-semibold {{ $page == $events->currentPage() ? 'bg-ink text-white' : 'bg-white border border-gray-200' }}">{{ $page }}</a>
        @endforeach
        @if($events->hasMorePages())<a href="{{ $events->nextPageUrl() }}" class="btn-ghost">Next <i class="ph ph-arrow-right"></i></a>@else<span class="btn-ghost opacity-50">Next <i class="ph ph-arrow-right"></i></span>@endif
    </div>
</div>
@endsection