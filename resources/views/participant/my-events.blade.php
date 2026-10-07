@extends('layouts.participant')
@section('title', 'My events')
@section('eyebrow', 'Registration')
@section('heading', 'My events & registrations')
@section('subtitle', 'Track your registrations, payments, and upcoming events.')

@section('content')
<div class="pcard p-8">
    <h2 class="text-lg font-bold">Races</h2>
    <div class="mt-4 divide-y divide-gray-200 border-t border-gray-200">
        @forelse($rows as $r)
            <div class="grid grid-cols-[2.2fr_0.8fr_1.3fr_1fr_1fr] items-center gap-4 py-5 text-sm">
                <div class="font-bold">{{ $r['event'] }}</div>
                <div class="font-bold">{{ $r['category'] }}</div>
                <div>
                    @php $cls = ['PAID' => 'bg-[#f1e0b3] text-[#7a5a12]', 'COMPLETED' => 'bg-[#dff0e3] text-[#1d6b3b]', 'PENDING PAYMENT' => 'bg-[#fdeee3] text-[#d9541c]'][$r['label']]; @endphp
                    <span class="chip {{ $cls }}">{{ $r['label'] }}</span>
                </div>
                <div class="text-gray-500">{{ $r['time'] }}</div>
                <div class="text-right font-bold">
                    @if($r['pending'])
                        <a href="/registrations/{{ $r['id'] }}/pay" class="hover:underline">Pay now →</a>
                    @else
                        <a href="/events/{{ $r['event_id'] }}" class="hover:underline">View results →</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="py-8 text-gray-500">You have no registrations yet. <a href="/events" class="font-bold text-ink underline">Find an event</a></p>
        @endforelse
    </div>
</div>
@endsection