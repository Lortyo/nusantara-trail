@extends('layouts.participant')
@section('title', $event->name)
@section('eyebrow', 'Event details')
@section('heading', $event->name)
@section('subtitle', optional($event->eventDate)->format('d M Y') . ' · ' . $event->startTime . ' · ' . data_get($event, 'location.venue') . ', ' . data_get($event, 'location.city'))

@section('content')
@php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="relative h-[280px] rounded-3xl overflow-hidden bg-gradient-to-br from-leaf to-ink">
    @if($event->bannerUrl)<img src="{{ $event->bannerUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover">@endif
</div>

@if($event->description)<p class="mt-6 text-gray-600 max-w-3xl leading-relaxed">{{ $event->description }}</p>@endif

@if($mine)
    <div class="mt-6 rounded-2xl bg-[#e3f1e7] text-[#1d6b3b] px-5 py-4 text-sm font-semibold flex items-center justify-between">
        <span>You are registered for this event ({{ $mine->registrationCode }}) · status: {{ strtoupper($mine->status) }}</span>
        <a href="{{ $mine->status === 'pending' ? '/registrations/' . $mine->getKey() . '/pay' : '/event-saya' }}" class="underline">
            {{ $mine->status === 'pending' ? 'Continue payment' : 'Go to My events' }}
        </a>
    </div>
@endif

<h2 id="categories" class="text-2xl font-bold mt-10 mb-5">Race categories</h2>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($cats as $c)
        @php
            $id = (string) $c->getKey();
            $left = (int) $c->slotsAvailable;
            $need = (bool) data_get($c, 'qualification.required');
            $okQ = in_array($id, $approved);
            $price = $isLocal ? data_get($c, 'price.local') : data_get($c, 'price.foreigner');
            $pct = $c->quota > 0 ? round(($c->quota - $left) / $c->quota * 100) : 0;
        @endphp
        <div class="pcard p-6 flex flex-col">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-4xl font-extrabold">{{ rtrim(rtrim(number_format($c->distanceKm, 1, '.', ''), '0'), '.') }}K</p>
                    <p class="text-gray-500 font-semibold">{{ $c->name }}</p>
                </div>
                @if($need)<span class="chip bg-[#fdeee3] text-[#d9541c]">QUALIFICATION</span>@endif
            </div>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Elevation</dt><dd class="font-bold">+{{ number_format($c->elevationGain, 0, ',', '.') }} m</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Cut-off</dt><dd class="font-bold">{{ $c->cutOffHours }} hours</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Price ({{ $isLocal ? 'local' : 'foreigner' }})</dt><dd class="font-bold">{{ $rp($price) }}</dd></div>
            </dl>
            <div class="mt-4">
                <div class="h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-ember" style="width: {{ $pct }}%"></div></div>
                <p class="text-xs text-gray-500 mt-1.5">{{ $left }} of {{ $c->quota }} slots left</p>
            </div>
            @if($need)
                <p class="text-xs text-gray-500 mt-3">Requires a finished race of {{ data_get($c, 'qualification.minDistanceKm') }} km or more within the last {{ data_get($c, 'qualification.withinYears') }} years.</p>
            @endif

            <div class="mt-auto pt-5">
                @if($mine)
                    <button class="btn-dark w-full" disabled>Already registered</button>
                @elseif(! in_array($state, ['open', 'spots_left']))
                    <button class="btn-dark w-full" disabled>Registration not open</button>
                @elseif($left <= 0)
                    <button class="btn-dark w-full" disabled>Sold out</button>
                @elseif($need && ! $okQ)
                    <button class="btn-dark w-full" disabled>Qualification required</button>
                @else
                    <form method="POST" action="/events/{{ $event->getKey() }}/register" class="space-y-3">
                        @csrf
                        <input type="hidden" name="category_id" value="{{ $id }}">
                        <select name="jersey_size" class="w-full h-11 rounded-xl border border-gray-200 px-3 text-sm">
                            @foreach(['XS','S','M','L','XL','XXL'] as $s)<option value="{{ $s }}" @selected($s === 'M')>Jersey size {{ $s }}</option>@endforeach
                        </select>
                        <button type="submit" class="btn-dark w-full">Register for {{ rtrim(rtrim(number_format($c->distanceKm, 1, '.', ''), '0'), '.') }}K</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection