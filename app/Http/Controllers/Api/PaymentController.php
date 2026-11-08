<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    private function fail(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    // POST /payments/checkout
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), ['registration_id' => 'required|string']);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = $request->user();
        $reg = Registration::where('_id', $request->input('registration_id'))
            ->where('user_id', (string) $user->getKey())->first();
        if (! $reg) {
            return $this->fail('Pendaftaran tidak ditemukan', 404);
        }
        if ($reg->status !== 'pending' || now()->gte($reg->expiresAt)) {
            return $this->fail('Reservasi sudah kedaluwarsa atau sudah dibayar', 409);
        }

        // Pakai ulang transaksi pending yang sudah punya snap token
        $payment = Payment::where('registration_id', (string) $reg->getKey())->where('status', 'pending')->first();

        if (! $payment || ! $payment->snapToken) {
            $payment = $payment ?: Payment::create([
                'registration_id' => (string) $reg->getKey(),
                'user_id' => (string) $user->getKey(),
                'orderId' => 'NTS-' . strtoupper(Str::random(8)) . '-' . time(),
                'purpose' => 'registration',
                'gateway' => 'midtrans',
                'amount' => (int) $reg->totalAmount,
                'status' => 'pending',
            ]);

            try {
                $itemName = optional(RaceEvent::find($reg->event_id))->name . ' ' . optional(RaceCategory::find($reg->category_id))->name;
                $payment->update(['snapToken' => $this->midtrans->snapToken($payment, $user, $itemName, $reg->expiresAt)]);
            } catch (\Throwable $e) {
                report($e);
                $payment->delete();

                return $this->fail(
                    config('app.debug') ? 'Payment error: ' . $e->getMessage() : 'Payment gateway error',
                    502
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi dibuat',
            'data' => [
                'orderId' => $payment->orderId,
                'snapToken' => $payment->snapToken,
                'amount' => (int) $payment->amount,
                'clientKey' => config('services.midtrans.client_key'),
            ],
        ], 201);
    }

    // GET /payments/{orderId}  (?sync=1 untuk menanyakan status langsung ke Midtrans)
    public function show(Request $request, string $orderId)
    {
        $payment = Payment::where('orderId', $orderId)->first();
        if (! $payment) {
            return $this->fail('Pembayaran tidak ditemukan', 404);
        }

        $isOwner = (string) $payment->user_id === (string) $request->user()->getKey();
        if (! $isOwner && $request->user()->role !== 'admin') {
            return $this->fail('Forbidden', 403);
        }

        if ($request->boolean('sync') && $payment->status === 'pending') {
            $status = $this->midtrans->status($orderId);
            if (isset($status['transaction_status'])) {
                $this->midtrans->apply($status);
                $payment = $payment->fresh();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'orderId' => $payment->orderId,
                'status' => $payment->status,
                'amount' => (int) $payment->amount,
                'paymentMethod' => $payment->paymentMethod,
                'paidAt' => optional($payment->paidAt)->toIso8601String(),
            ],
        ]);
    }

    // POST /payments/notification  (webhook Midtrans, tanpa JWT, diverifikasi signature)
    public function notification(Request $request)
    {
        $payload = $request->all();

        if (! $this->midtrans->validSignature($payload)) {
            return $this->fail('Signature tidak valid', 403);
        }

        $this->midtrans->apply($payload);

        return response()->json(['success' => true, 'message' => 'Notifikasi diproses']);
    }
}