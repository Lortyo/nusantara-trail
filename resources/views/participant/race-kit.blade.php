@extends('layouts.participant')
@section('title', 'Race kit')
@section('eyebrow', 'Race week')
@section('heading', 'Race kit collection')
@section('subtitle', 'Save your QR credential and follow your selected collection schedule.')

@section('content')
@if(! $ticket)
    <div class="pcard p-10 text-gray-500">
        Your race kit and QR credential appear here after your payment is confirmed.
        <a href="/events" class="font-bold text-ink underline">Find an event</a>
    </div>
@else
@php
    $km = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    $tz = ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT'][$event->timezone] ?? 'WIB';
    $hasSchedule = $event->kitDate && $event->kitVenue;
    $collected = $ticket->kitCollectedAt;
    $mapUrl = $event->kitMapUrl ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event->kitVenue . ' ' . $event->kitAddress);
    $dot = fn ($t) => str_replace(':', '.', $t);
    $codeLabel = $reg->registrationCode;
@endphp

@if(count($options) > 1)
    <select onchange="location.href='/race-kit?registration=' + this.value" class="mb-5 h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold">
        @foreach($options as $id => $name)<option value="{{ $id }}" @selected((string) $reg->getKey() === (string) $id)>{{ $name }}</option>@endforeach
    </select>
@endif

<div class="grid xl:grid-cols-[1fr_380px] gap-7 items-start">
    <div class="space-y-6">

        <div class="pcard p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-[26px] font-bold">{{ $collected ? 'Race kit collected' : ($hasSchedule ? 'Collection slot confirmed' : 'Collection schedule coming soon') }}</h2>
                    <p class="text-gray-500 mt-1">
                        {{ $collected ? 'Collected on ' . $collected->format('d F Y, H:i') . '.' : ($hasSchedule ? 'Show your QR credential to the check-in staff.' : 'The committee has not announced the collection schedule yet.') }}
                    </p>
                </div>
                <span class="chip {{ $collected ? 'bg-[#dff0e3] text-[#1d6b3b]' : ($hasSchedule ? 'bg-[#e3f1e7] text-[#1d6b3b]' : 'bg-gray-100 text-gray-500') }}">
                    {{ $collected ? 'COLLECTED' : ($hasSchedule ? 'SCHEDULED' : 'TBA') }}
                </span>
            </div>

            @if($hasSchedule)
                <div class="grid md:grid-cols-3 gap-4 mt-6">
                    <div class="rounded-2xl bg-[#efefea] px-5 py-4"><p class="text-[11px] font-bold uppercase text-gray-500">Date</p><p class="font-bold text-lg mt-1">{{ $event->kitDate->format('l, j F Y') }}</p></div>
                    <div class="rounded-2xl bg-[#efefea] px-5 py-4"><p class="text-[11px] font-bold uppercase text-gray-500">Time</p><p class="font-bold text-lg mt-1">{{ $dot($event->kitStartTime) }}–{{ $dot($event->kitEndTime) }} {{ $tz }}</p></div>
                    <div class="rounded-2xl bg-[#efefea] px-5 py-4"><p class="text-[11px] font-bold uppercase text-gray-500">Location</p><p class="font-bold text-lg mt-1">{{ $event->kitVenue }}</p></div>
                </div>
                <p class="mt-5 flex items-center gap-2 text-[15px] font-bold text-leaf">
                    <i class="ph-fill ph-map-pin text-xl text-ember"></i>
                    {{ $event->kitAddress }} <a href="{{ $mapUrl }}" target="_blank" class="underline">• Open map →</a>
                </p>
            @endif
        </div>

        <div class="pcard p-8">
            <h2 class="text-[22px] font-bold">{{ $km($cat->distanceKm) }}K race kit contents</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 mt-5">
                <div class="rounded-xl border border-gray-200 p-4"><p class="font-bold text-sm">BIB</p><p class="text-sm text-gray-500 mt-1">{{ $ticket->bibNumber }}</p></div>
                <div class="rounded-xl border border-gray-200 p-4"><p class="font-bold text-sm">Jersey</p><p class="text-sm text-gray-500 mt-1">Size {{ $reg->jerseySize }}</p></div>
                @foreach($benefits as $b)
                    <div class="rounded-xl border border-gray-200 p-4"><p class="font-bold text-sm">{{ ucfirst($b) }}</p><p class="text-sm text-gray-500 mt-1">Included</p></div>
                @endforeach
            </div>
        </div>

        <div class="pcard p-8">
            <h2 class="text-[22px] font-bold">What to bring</h2>
            <p class="text-sm text-gray-500">Tap a status to mark an item as ready.</p>
            <div class="mt-4 space-y-2">
                @foreach([['id', 'Original ID matching your registration', 0], ['gear', 'Mandatory gear for your race category', 0], ['qr', 'QR race credential in the participant portal', 1], ['proxy', 'Authorization letter for proxy collection', 0]] as $n => [$key, $text, $default])
                    <div data-check="{{ $key }}" data-default="{{ $default }}" class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-4">
                            <span class="w-9 h-9 rounded-full bg-[#e3f1e7] text-[#1d6b3b] font-bold text-sm flex items-center justify-center">{{ $n + 1 }}</span>
                            <span class="text-[17px]">{{ $text }}</span>
                        </div>
                        <button type="button" class="chip">CHECK</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl bg-[#fdeee3] p-6">
            <p class="font-bold text-ember text-lg">Important note</p>
            <p class="text-[#a8571c] text-sm mt-2">This QR code can only be used once. Do not share screenshots with others.</p>
        </div>

        <div class="pcard p-8 text-center">
            <p class="text-[13px] font-bold uppercase text-ember">{{ $event->name }}</p>
            <p class="text-5xl font-medium text-leaf mt-2">{{ $ticket->bibNumber }}</p>
            <p class="text-gray-500 mt-2">{{ $km($cat->distanceKm) }}K • {{ optional($profile)->fullName ?? auth()->user()->name }}</p>
            <img src="{{ $qr }}" alt="QR credential" class="w-[280px] h-[280px] mx-auto mt-6">
            <p class="text-sm text-gray-500 mt-3">{{ $codeLabel }}</p>
            <a href="{{ $qr }}" download="qr-bib-{{ $ticket->bibNumber }}.svg" class="btn-dark mt-5 px-8">Download QR</a>
        </div>
    </div>
</div>

<script>
    const ready = 'bg-[#dff0e3] text-[#1d6b3b]', check = 'bg-[#fdf0d5] text-[#8a5a12]';
    document.querySelectorAll('[data-check]').forEach(row => {
        const chip = row.querySelector('.chip');
        const key = 'kit-{{ $ticket->getKey() }}-' + row.dataset.check;
        const set = (on) => { chip.textContent = on ? 'READY' : 'CHECK'; chip.className = 'chip ' + (on ? ready : check); };
        const saved = localStorage.getItem(key);
        set(saved === null ? row.dataset.default === '1' : saved === '1');
        chip.addEventListener('click', () => {
            const on = chip.textContent !== 'READY';
            localStorage.setItem(key, on ? '1' : '0');
            set(on);
        });
    });
</script>
@endif
@endsection