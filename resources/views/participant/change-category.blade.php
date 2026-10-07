@extends('layouts.participant')
@section('title', 'Change category')
@section('eyebrow', 'Registration management')
@section('heading', 'Upgrade / downgrade category')
@section('subtitle', 'Choose a new category and review the price difference before confirming.')
@section('pill', isset($event) ? $event->name : 'Nusantara Trail Series')

@section('content')
@if(! $reg)
    <div class="pcard p-10 text-gray-500">You have no paid registration that can be changed. <a href="/events" class="font-bold text-ink underline">Find an event</a></div>
@else
@php
    $rp = fn ($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
    $km = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    $daysLeft = $deadline ? max(0, (int) ceil(now()->diffInHours($deadline, false) / 24)) : 0;
@endphp

<div class="rounded-2xl border border-[#f1dfb8] bg-[#fff8e8] px-6 py-4 flex items-center justify-between text-[#8a5a12]">
    <span class="flex items-center gap-3"><i class="ph ph-warning text-2xl"></i>
        @if($deadline) Category changes {{ $open ? 'close' : 'closed' }} on {{ $deadline->format('d F Y, H:i') }} WIB. Subject to availability and eligibility.
        @else Category changes are not available for this event. @endif
    </span>
    @if($open)<span class="chip bg-[#f8e7c2] uppercase">{{ $daysLeft }} days left</span>@endif
</div>

@if($pending)
    <div class="mt-4 rounded-2xl bg-[#fdeee3] text-[#d9541c] px-6 py-3 text-sm font-semibold">
        You have a category change waiting for payment. <a href="/category-changes/{{ $pending->getKey() }}/pay" class="underline">Continue payment</a>
    </div>
@endif

<div class="grid md:grid-cols-3 gap-6 mt-6">
    @foreach($cats as $c)
        @php
            $isCurrent = (string) $c->getKey() === (string) $current->getKey();
            $isUp = $c->distanceKm > $current->distanceKm;
            $price = $isLocal ? data_get($c, 'price.local') : data_get($c, 'price.foreigner');
            $isSel = $selected && (string) $selected->getKey() === (string) $c->getKey();
            $href = '/change-category?registration=' . $reg->getKey() . '&to=' . $c->getKey();
        @endphp
        <div class="rounded-3xl p-7 {{ $isCurrent ? 'bg-ink text-white' : 'bg-white border border-black/5 shadow-[0_6px_24px_rgba(12,32,22,0.06)]' }} {{ $isSel ? 'ring-2 ring-ember' : '' }}">
            <div class="flex items-start justify-between">
                <p class="text-5xl font-extrabold {{ $isCurrent ? 'text-lime' : 'text-ink' }}">{{ $km($c->distanceKm) }}K</p>
                @if($isCurrent)<span class="chip bg-[#e3f1e7] text-[#1d6b3b]">ACTIVE</span>
                @elseif($isUp)<span class="chip bg-[#fdeee3] text-[#d9541c]">UPGRADE</span>
                @else<span class="chip bg-[#e3f1e7] text-[#1d6b3b]">DOWNGRADE</span>@endif
            </div>
            <p class="text-lg font-medium mt-1 {{ $isCurrent ? 'text-white/80' : 'text-gray-500' }}">{{ $c->name }}</p>
            <dl class="mt-5 space-y-2.5 text-[15px] {{ $isCurrent ? 'text-white/70' : 'text-gray-500' }}">
                <div class="flex justify-between"><dt>Elevation</dt><dd class="font-bold {{ $isCurrent ? 'text-white' : 'text-ink' }}">+{{ number_format($c->elevationGain, 0, ',', '.') }} m</dd></div>
                <div class="flex justify-between"><dt>Cut-off</dt><dd class="font-bold {{ $isCurrent ? 'text-white' : 'text-ink' }}">{{ $c->cutOffHours }} hours</dd></div>
                <div class="flex justify-between"><dt>Price</dt><dd class="font-bold {{ $isCurrent ? 'text-white' : 'text-ink' }}">{{ $rp($price) }}</dd></div>
            </dl>
            <div class="mt-6">
                @if($isCurrent)
                    <span class="flex h-14 items-center justify-center rounded-xl bg-leaf font-bold">Current category</span>
                @elseif(! $open || $pending || $c->slotsAvailable <= 0)
                    <span class="flex h-14 items-center justify-center rounded-xl bg-gray-100 text-gray-400 font-bold">{{ $c->slotsAvailable <= 0 ? 'Sold out' : 'Unavailable' }}</span>
                @else
                    <a href="{{ $href }}" class="flex h-14 items-center justify-center rounded-xl font-bold {{ $isUp ? 'bg-ember text-white hover:opacity-90' : 'bg-[#eeeee9] text-ink hover:bg-gray-200' }}">
                        {{ $isUp ? 'Select upgrade' : 'Select downgrade' }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>

@if($preview)
<form method="POST" action="/change-category" enctype="multipart/form-data" class="grid lg:grid-cols-[1.5fr_1fr] gap-6 mt-6">
    @csrf
    <input type="hidden" name="registration_id" value="{{ $reg->getKey() }}">
    <input type="hidden" name="to_category_id" value="{{ $selected->getKey() }}">

    <div class="pcard p-8">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Change summary</h2>
            <span class="chip bg-[#fdeee3] text-[#d9541c] uppercase">{{ $km($selected->distanceKm) }}K preview</span>
        </div>
        <div class="mt-5 divide-y divide-gray-100 text-[15px]">
            <div class="flex justify-between py-4"><span class="text-gray-500">Current category</span><span class="font-bold">{{ $km($current->distanceKm) }}K {{ $current->name }}</span></div>
            <div class="flex justify-between py-4"><span class="text-gray-500">New category</span><span class="font-bold">{{ $km($selected->distanceKm) }}K {{ $selected->name }}</span></div>
            <div class="flex justify-between py-4"><span class="text-gray-500">Price difference</span><span class="font-bold">{{ $rp($preview['diff']) }}</span></div>
            <div class="flex justify-between py-4"><span class="text-gray-500">Administration fee</span><span class="font-bold">{{ $rp($preview['fee']) }}</span></div>
            <div class="flex justify-between py-5"><span class="font-bold">Total payment</span><span class="text-3xl font-extrabold text-ember">{{ $rp($preview['total']) }}</span></div>
        </div>
        @if($preview['diff'] < 0)<p class="text-xs text-gray-500">The price difference of a downgrade is not charged. Refunds are handled manually by the committee.</p>@endif
    </div>

    <div class="pcard p-8 h-fit">
        <h2 class="text-2xl font-bold">{{ $km($selected->distanceKm) }}K {{ $preview['isUpgrade'] ? 'upgrade' : 'downgrade' }} requirements</h2>
        <div class="mt-5 space-y-5">
            @forelse($preview['requirements'] as $req)
                <div class="flex gap-3">
                    @if($req['ok'] === true)<i class="ph ph-seal-check text-2xl text-[#1f8f55]"></i>
                    @elseif($req['ok'] === false)<i class="ph ph-x-circle text-2xl text-red-500"></i>
                    @else<i class="ph ph-cloud-arrow-up text-2xl text-ember"></i>@endif
                    <div>
                        <p class="font-bold">{{ $req['title'] }}</p>
                        <p class="text-sm {{ $req['ok'] === true ? 'text-[#1f8f55]' : ($req['ok'] === false ? 'text-red-500' : 'text-ember') }}">{{ $req['note'] }}</p>
                        @if($req['ok'] === null)
                            <input type="file" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png"
                                   class="mt-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#eeeee9] file:px-3 file:py-2 file:font-semibold">
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No additional requirements for this change.</p>
            @endforelse
        </div>
        @php $blocked = collect($preview['requirements'])->contains(fn ($r) => $r['ok'] === false); @endphp
        <button type="submit" class="btn-dark w-full h-14 mt-6" @disabled($blocked || ! $open || $pending)>
            {{ $preview['isUpgrade'] ? 'Upload document & continue' : 'Confirm change & continue' }} <i class="ph ph-arrow-right"></i>
        </button>
    </div>
</form>
@else
    <div class="pcard p-8 mt-6 text-gray-500">Select a category above to preview the price difference and requirements.</div>
@endif
@endif
@endsection