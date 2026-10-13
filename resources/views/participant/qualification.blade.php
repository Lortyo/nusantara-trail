@extends('layouts.participant')
@section('title', 'Qualification')
@section('eyebrow', 'Entry requirement')
@section('heading', 'Qualification')
@section('subtitle', 'Submit proof of a previous finish for categories that require experience.')

@section('content')
@if(! $selected)
    <div class="pcard p-10 text-gray-500">No open event has a category that requires qualification right now. <a href="/events" class="font-bold text-ink underline">Back to events</a></div>
@else
@php
    $cat = $selected['cat']; $ev = $selected['event'];
    $chip = ['pending' => 'bg-[#fdf0d5] text-[#8a5a12]', 'approved' => 'bg-[#dff0e3] text-[#1d6b3b]', 'rejected' => 'bg-[#fde8e8] text-[#b42318]'];
@endphp
<div class="grid xl:grid-cols-[1.3fr_1fr] gap-7 items-start">
    <div class="pcard p-8">
        <label class="text-[11px] font-bold uppercase text-gray-500">Category</label>
        <select onchange="location.href='/qualification?category=' + this.value" class="mt-2 w-full h-12 rounded-xl border border-gray-200 px-4 font-semibold">
            @foreach($options as $o)
                <option value="{{ $o['cat']->getKey() }}" @selected((string) $o['cat']->getKey() === (string) $cat->getKey())>
                    {{ $o['event']->name }} · {{ rtrim(rtrim(number_format($o['cat']->distanceKm, 1, '.', ''), '0'), '.') }}K {{ $o['cat']->name }}
                </option>
            @endforeach
        </select>

        <div class="mt-5 rounded-2xl bg-[#e8f1ea] px-5 py-4">
            <p class="font-bold">Requirement: {{ $req['min'] }} km within the last {{ $req['years'] }} years</p>
            <p class="text-sm text-gray-600 mt-1">Your race finish must be on or after {{ $req['earliest']->format('d M Y') }}.</p>
        </div>

        @if($existing)
            <div class="mt-6 rounded-2xl bg-[#fdf0d5] text-[#8a5a12] px-5 py-4 text-sm font-semibold">
                You already have a {{ $existing->status }} request ({{ $existing->requestCode }}) for this category.
                @if($existing->status === 'approved') <a href="/events/{{ $ev->getKey() }}" class="underline">Go register</a> @endif
            </div>
        @else
            <form method="POST" action="/qualification" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="category_id" value="{{ $cat->getKey() }}">
                <div>
                    <label class="text-sm font-bold">Race name *</label>
                    <input name="race_name" value="{{ old('race_name') }}" class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4" placeholder="Doi Suthep Ultra 2025">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="text-sm font-bold">Race date *</label><input type="date" name="race_date" value="{{ old('race_date') }}" class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4"></div>
                    <div><label class="text-sm font-bold">Distance (km) *</label><input type="number" step="any" name="distance_km" value="{{ old('distance_km') }}" class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4"></div>
                    <div><label class="text-sm font-bold">Finish time *</label><input name="finish_time" value="{{ old('finish_time') }}" placeholder="HH:MM:SS" class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="text-sm font-bold">BIB number in that race</label><input name="race_bib" value="{{ old('race_bib') }}" class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4"></div>
                    <div><label class="text-sm font-bold">Official result link</label><input name="result_url" value="{{ old('result_url') }}" placeholder="https://..." class="mt-1.5 w-full h-12 rounded-xl border border-gray-200 px-4"></div>
                </div>
                <div>
                    <label class="text-sm font-bold">Proof of finish * <span class="font-normal text-gray-500">(certificate or official result, PDF/JPG/PNG, max 5 MB)</span></label>
                    <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" class="mt-1.5 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#eeeee9] file:px-4 file:py-2.5 file:font-semibold">
                </div>
                <button type="submit" class="btn-dark w-full h-12">Submit for review <i class="ph ph-arrow-right"></i></button>
            </form>
        @endif
    </div>

    <div class="pcard p-8">
        <h2 class="text-xl font-bold">My submissions</h2>
        <div class="mt-4 divide-y divide-gray-100">
            @forelse($mine as $m)
                <div class="py-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-sm">{{ $m['label'] }}</p>
                        <span class="chip {{ $chip[$m['status']] }} uppercase">{{ $m['status'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $m['code'] }} · {{ $m['race'] }} · {{ $m['date'] }}</p>
                    @if($m['note'])<p class="text-sm mt-2 rounded-xl bg-[#f4f5f0] px-3 py-2"><span class="font-bold">Committee note:</span> {{ $m['note'] }}</p>@endif
                </div>
            @empty
                <p class="text-sm text-gray-500 py-4">You have not submitted any qualification yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endif
@endsection