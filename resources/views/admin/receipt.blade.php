<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $reg->registrationCode }}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 560px; margin: 40px auto; color: #0c2016; }
        h1 { font-size: 22px; margin-bottom: 4px; } .muted { color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        td { padding: 10px 0; border-bottom: 1px solid #ddd; font-size: 14px; } td:last-child { text-align: right; font-weight: bold; }
        .total td { font-size: 18px; border-bottom: 0; } button { margin-top: 24px; padding: 10px 18px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <h1>Nusantara Trail Series</h1>
    <p class="muted">Payment receipt · {{ $reg->registrationCode }}</p>
    <table>
        <tr><td>Participant</td><td>{{ optional($user)->name }}</td></tr>
        <tr><td>Email</td><td>{{ optional($user)->email }}</td></tr>
        <tr><td>Event</td><td>{{ optional($event)->name }}</td></tr>
        <tr><td>Category</td><td>{{ rtrim(rtrim(number_format(optional($cat)->distanceKm, 1, '.', ''), '0'), '.') }}K {{ optional($cat)->name }}</td></tr>
        <tr><td>Order ID</td><td>{{ $payment->orderId }}</td></tr>
        <tr><td>Method</td><td>{{ ucwords(str_replace('_', ' ', (string) $payment->paymentMethod)) }}</td></tr>
        <tr><td>Paid on</td><td>{{ optional($payment->paidAt)->format('d M Y, H:i') }}</td></tr>
        <tr class="total"><td>Total paid</td><td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
    </table>
    <button onclick="window.print()">Print</button>
</body>
</html>