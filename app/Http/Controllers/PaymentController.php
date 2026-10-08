<?php

namespace App\Http\Controllers;

use App\Models\CategoryChange;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private MidtransService $midtrans)
    {
    }

    private function makePayment(string $purpose, string $refId, int $amount, $user, string $name, $expiresAt): Payment
    {
        $field = $purpose === 'upgrade' ? 'change_id' : 'registration_id';

        $existing = Payment::where($field, $refId)->where('status', 'pending')->first();
        if ($existing && $existing->snapToken)
            return $existing;

        $payment = Payment::create([
            $field => $refId,
            'user_id' => (string) $user->getKey(),
            'orderId' => 'NTS-' . strtoupper(Str::random(8)) . '-' . time(),
            'purpose' => $purpose,
            'gateway' => 'midtrans',
            'amount' => $amount,
            'status' => 'pending',
        ]);
        $payment->update(['snapToken' => $this->midtrans->snapToken($payment, $user, $name, $expiresAt)]);

        return $payment;
    }

    public function checkoutRegistration(Request $request, string $id)
    {
        $reg = Registration::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->firstOrFail();
        if ($reg->status !== 'pending' || now()->gte($reg->expiresAt)) {
            return response()->json(['message' => 'This reservation has expired.'], 409);
        }

        try {
            $name = RaceEvent::find($reg->event_id)->name . ' ' . RaceCategory::find($reg->category_id)->name;
            $p = $this->makePayment('registration', (string) $reg->getKey(), (int) $reg->totalAmount, $request->user(), $name, $reg->expiresAt);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => config('app.debug')
                    ? 'Payment error: ' . $e->getMessage()
                    : 'Payment gateway error. Check your Midtrans keys in .env.',
            ], 502);
        }

        return response()->json(['token' => $p->snapToken, 'orderId' => $p->orderId]);
    }

    public function checkoutChange(Request $request, string $id)
    {
        $chg = CategoryChange::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->firstOrFail();
        if ($chg->status !== 'pending_payment' || now()->gte($chg->expiresAt)) {
            return response()->json(['message' => 'This change request has expired.'], 409);
        }

        try {
            $p = $this->makePayment('upgrade', (string) $chg->getKey(), (int) $chg->total, $request->user(), 'Category change', $chg->expiresAt);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => config('app.debug')
                    ? 'Payment error: ' . $e->getMessage()
                    : 'Payment gateway error. Check your Midtrans keys in .env.',
            ], 502);
        }

        return response()->json(['token' => $p->snapToken, 'orderId' => $p->orderId]);
    }

    // Dipanggil browser setelah popup Midtrans ditutup: cek status langsung ke Midtrans
    public function sync(Request $request, string $orderId)
    {
        $payment = Payment::where('orderId', $orderId)->where('user_id', (string) $request->user()->getKey())->firstOrFail();

        $status = $this->midtrans->status($orderId);
        if (isset($status['transaction_status']))
            $this->midtrans->apply($status);

        return response()->json(['status' => $payment->fresh()->status]);
    }

    // Webhook dari Midtrans (butuh URL publik, misalnya ngrok)
    public function notification(Request $request)
    {
        $payload = $request->all();
        if (!$this->midtrans->validSignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }
        $this->midtrans->apply($payload);

        return response()->json(['message' => 'OK']);
    }
}