@extends('layouts.participant')
@section('title', 'Payment')
@section('eyebrow', 'Payment')
@section('heading', $heading)
@section('subtitle', 'Pay securely with Midtrans. Your slot is held until the timer ends.')

@section('content')
<div class="grid lg:grid-cols-[1.4fr_1fr] gap-6 max-w-5xl">
    <div class="pcard p-8">
        <h2 class="text-xl font-bold mb-4">Order summary</h2>
        @foreach($lines as [$label, $value])
            <div class="flex justify-between py-3.5 border-b border-gray-100 text-[15px]">
                <span class="text-gray-500">{{ $label }}</span><span class="font-semibold text-right">{{ $value }}</span>
            </div>
        @endforeach
        <div class="flex justify-between items-center pt-5">
            <span class="font-bold">Total payment</span>
            <span class="text-2xl font-extrabold text-ember">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="pcard p-8 h-fit">
        @if($active)
            <p class="text-sm text-gray-500">Complete payment within</p>
            <p id="timer" class="text-5xl font-extrabold tracking-tight mt-1">--:--</p>
            <button id="pay-btn" class="btn-dark w-full mt-6">Pay with Midtrans</button>
            @if($cancelUrl)
                <form method="POST" action="{{ $cancelUrl }}" class="mt-3" onsubmit="return confirm('Cancel this reservation and release your slot?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ghost w-full">Cancel reservation</button>
                </form>
            @endif
        @else
            <p class="font-bold text-lg">This reservation has expired.</p>
            <p class="text-sm text-gray-500 mt-2">The slot was released so other runners can register.</p>
            <a href="/events" class="btn-dark w-full mt-6">Back to events</a>
        @endif
    </div>
</div>

@if($active)
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com' }}/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    const csrf = '{{ csrf_token() }}';
    const end = new Date('{{ $expiresAt->toIso8601String() }}').getTime();
    const timer = document.getElementById('timer');
    setInterval(() => {
        const s = Math.max(0, Math.floor((end - Date.now()) / 1000));
        timer.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        if (s === 0) location.reload();
    }, 1000);

    const post = (url) => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    const finish = async (orderId) => { try { await post('/payments/' + orderId + '/sync'); } catch (e) {} location.href = '/event-saya'; };

    const btn = document.getElementById('pay-btn');
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        const res = await post('{{ $checkoutUrl }}');
        const data = await res.json();
        if (!res.ok) { alert(data.message || 'Payment error'); btn.disabled = false; return; }
        window.snap.pay(data.token, {
            onSuccess: () => finish(data.orderId),
            onPending: () => finish(data.orderId),
            onError: () => { alert('Payment failed, please try again.'); btn.disabled = false; },
            onClose: () => { btn.disabled = false; },
        });
    });
</script>
@endif
@endsection