<?php

namespace App\Services;

use App\Models\CategoryChange;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;
use MongoDB\Operation\FindOneAndUpdate;

class MidtransService
{
    private function key(): string { return (string) config('services.midtrans.server_key'); }
    private function snapUrl(): string { return config('services.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'; }
    private function apiUrl(): string { return config('services.midtrans.is_production') ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com'; }

    // Minta snap token ke Midtrans
    public function snapToken(Payment $payment, User $user, string $itemName, $expiresAt): string
    {
        $minutes = max(1, (int) ceil(now()->diffInSeconds($expiresAt, false) / 60));

        $res = Http::withBasicAuth($this->key(), '')->acceptJson()->post($this->snapUrl() . '/snap/v1/transactions', [
            'transaction_details' => ['order_id' => $payment->orderId, 'gross_amount' => (int) $payment->amount],
            'customer_details' => ['first_name' => $user->name, 'email' => $user->email],
            'item_details' => [[
                'id' => $payment->orderId, 'price' => (int) $payment->amount, 'quantity' => 1,
                'name' => Str::limit($itemName, 50, ''),
            ]],
            'expiry' => ['unit' => 'minute', 'duration' => $minutes],
        ]);
        $res->throw();

        return (string) $res->json('token');
    }

    // Tanya status transaksi langsung ke Midtrans (dipakai tanpa perlu webhook saat development)
    public function status(string $orderId): array
    {
        return Http::withBasicAuth($this->key(), '')->acceptJson()
            ->get($this->apiUrl() . "/v2/{$orderId}/status")->json() ?? [];
    }

    public function validSignature(array $p): bool
    {
        $expected = hash('sha512', ($p['order_id'] ?? '') . ($p['status_code'] ?? '') . ($p['gross_amount'] ?? '') . $this->key());
        return hash_equals($expected, (string) ($p['signature_key'] ?? ''));
    }

    // Terapkan status dari Midtrans. Aman dipanggil berulang (idempoten).
    public function apply(array $p): void
    {
        $payment = Payment::where('orderId', $p['order_id'] ?? '')->first();
        if (! $payment) return;

        $t = $p['transaction_status'] ?? 'pending';
        $fraud = $p['fraud_status'] ?? 'accept';
        $paid = $t === 'settlement' || ($t === 'capture' && $fraud === 'accept');

        if ($paid) {
            $changed = Payment::where('_id', $payment->getKey())->where('status', '!=', 'settlement')->update([
                'status' => 'settlement', 'paidAt' => now(),
                'paymentMethod' => $p['payment_type'] ?? null, 'transactionId' => $p['transaction_id'] ?? null,
            ]);
            if ($changed) $this->onPaid($payment);
        } elseif (in_array($t, ['expire', 'cancel', 'deny', 'failure'])) {
            $changed = Payment::where('_id', $payment->getKey())->where('status', 'pending')
                ->update(['status' => $t === 'failure' ? 'deny' : $t]);
            if ($changed) $this->onFailed($payment);
        } else {
            $payment->update(['paymentMethod' => $p['payment_type'] ?? $payment->paymentMethod]);
        }
    }

    private function onPaid(Payment $payment): void
    {
        if ($payment->purpose === 'upgrade') {
            $this->completeChange($payment);
            return;
        }

        $reg = Registration::find($payment->registration_id);
        if (! $reg || $reg->status === 'paid') return;

        $reg->update(['status' => 'paid']);
        $this->issueTicket($reg);
    }

    private function onFailed(Payment $payment): void
    {
        if ($payment->purpose === 'upgrade') {
            $chg = CategoryChange::find($payment->change_id);
            if ($chg) $this->cancelChange($chg);
            return;
        }
        $this->releaseRegistration(Registration::find($payment->registration_id), 'expired');
    }

    // Buat nomor BIB (berurutan per event) dan QR token
    private function issueTicket(Registration $reg): void
    {
        if (Ticket::where('registration_id', (string) $reg->getKey())->exists()) return;

        $event = RaceEvent::raw(fn ($c) => $c->findOneAndUpdate(
            ['_id' => new ObjectId((string) $reg->event_id)],
            ['$inc' => ['bibSequence' => 1]],
            ['returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
        ));
        $seq = (int) ($event['bibSequence'] ?? 1);

        Ticket::create([
            'registration_id' => (string) $reg->getKey(),
            'event_id' => (string) $reg->event_id,
            'bibNumber' => (string) (1000 + $seq),
            'qrToken' => Str::random(40),
            'issuedAt' => now(),
        ]);
    }

    private function completeChange(Payment $payment): void
    {
        $chg = CategoryChange::find($payment->change_id);
        if (! $chg) return;

        $done = CategoryChange::where('_id', $chg->getKey())->where('status', 'pending_payment')->update(['status' => 'completed']);
        if (! $done) return;

        RaceCategory::where('_id', $chg->from_category_id)->increment('slotsAvailable');
        Registration::where('_id', $chg->registration_id)->update([
            'category_id' => $chg->to_category_id,
            'totalAmount' => (int) $chg->newPrice,
        ]);
    }

    public function cancelChange(CategoryChange $chg): void
    {
        $done = CategoryChange::where('_id', $chg->getKey())->where('status', 'pending_payment')->update(['status' => 'cancelled']);
        if ($done) RaceCategory::where('_id', $chg->to_category_id)->increment('slotsAvailable');
    }

    // Kembalikan slot jika reservasi belum dibayar
    public function releaseRegistration(?Registration $reg, string $status): void
    {
        if (! $reg) return;

        $changed = Registration::where('_id', $reg->getKey())->where('status', 'pending')->update(['status' => $status]);
        if ($changed) RaceCategory::where('_id', $reg->category_id)->increment('slotsAvailable');
    }
}