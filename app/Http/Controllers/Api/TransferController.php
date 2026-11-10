<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Http\Controllers\Controller;
use App\Models\BibTransfer;
use App\Models\CategoryChange;
use App\Models\Profile;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    use ParticipantHelpers;

    private function fail(string $message, int $status, array $extra = [])
    {
        return response()->json(array_merge(['success' => false, 'message' => $message], $extra), $status);
    }

    private function hasActiveRegistration(string $uid, string $eventId): bool
    {
        return Registration::where('user_id', $uid)->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'paid'])->exists();
    }

    private function profileComplete(string $uid): bool
    {
        $profile = Profile::where('user_id', $uid)->first();
        if (! $profile) {
            return false;
        }
        foreach (RegistrationController::REQUIRED_PROFILE_FIELDS as $field) {
            if (blank($profile->{$field})) {
                return false;
            }
        }

        return true;
    }

    // Syarat yang harus terpenuhi saat mengajukan dan saat menyetujui. Mengembalikan pesan jika terhalang.
    private function blocker(Registration $reg): ?string
    {
        if ($reg->status !== 'paid') {
            return 'Hanya pendaftaran yang sudah lunas yang dapat dipindahtangankan';
        }

        $event = RaceEvent::find($reg->event_id);
        $deadline = $event ? ($event->transferDeadline ?? $event->eventDate) : null;
        if (! $deadline || now()->gte($deadline)) {
            return 'Batas waktu transfer BIB sudah lewat';
        }

        $ticket = Ticket::where('registration_id', (string) $reg->getKey())->first();
        if ($ticket && $ticket->kitCollectedAt) {
            return 'Race kit sudah diambil, BIB tidak dapat dipindahtangankan';
        }

        $changing = CategoryChange::where('registration_id', (string) $reg->getKey())
            ->where('status', 'pending_payment')->where('expiresAt', '>', now())->exists();
        if ($changing) {
            return 'Ada perubahan kategori yang menunggu pembayaran';
        }

        return null;
    }

    // GET /transfers  (pengajuan masuk dan keluar milik saya)
    public function index(Request $request)
    {
        $uid = (string) $request->user()->getKey();
        $transfers = BibTransfer::where('from_user_id', $uid)->orWhere('to_user_id', $uid)
            ->orderBy('created_at', 'desc')->get();

        $regs = Registration::whereIn('_id', $transfers->pluck('registration_id')->unique()->all())->get()
            ->keyBy(fn ($r) => (string) $r->getKey());
        $events = RaceEvent::whereIn('_id', $regs->pluck('event_id')->unique()->all())->get()
            ->keyBy(fn ($e) => (string) $e->getKey());
        $cats = RaceCategory::whereIn('_id', $regs->pluck('category_id')->unique()->all())->get()
            ->keyBy(fn ($c) => (string) $c->getKey());
        $tickets = Ticket::whereIn('registration_id', $regs->keys()->all())->get()
            ->keyBy(fn ($t) => (string) $t->registration_id);
        $userIds = $transfers->flatMap(fn ($t) => [(string) $t->from_user_id, (string) $t->to_user_id])->unique()->all();
        $users = User::whereIn('_id', $userIds)->get()->keyBy(fn ($u) => (string) $u->getKey());

        $data = $transfers->map(function ($t) use ($uid, $regs, $events, $cats, $tickets, $users) {
            $reg = $regs[(string) $t->registration_id] ?? null;
            $outgoing = (string) $t->from_user_id === $uid;
            $other = $users[(string) ($outgoing ? $t->to_user_id : $t->from_user_id)] ?? null;
            $cat = $reg ? ($cats[(string) $reg->category_id] ?? null) : null;

            return [
                'id' => (string) $t->getKey(),
                'direction' => $outgoing ? 'outgoing' : 'incoming',
                'status' => $t->status,
                'registration_id' => (string) $t->registration_id,
                'event' => $reg ? optional($events[(string) $reg->event_id] ?? null)->name : null,
                'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : null,
                'bibNumber' => $reg ? optional($tickets[(string) $reg->getKey()] ?? null)->bibNumber : null,
                'counterpart' => $outgoing ? optional($other)->email : optional($other)->name,
                'createdAt' => optional($t->created_at)->toIso8601String(),
                'respondedAt' => optional($t->respondedAt)->toIso8601String(),
            ];
        })->values();

        return response()->json(['success' => true, 'message' => 'OK', 'data' => $data]);
    }

    // POST /registrations/{id}/transfer
    public function store(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), ['recipient_email' => 'required|email|max:255']);
        if ($validator->fails()) {
            return $this->fail('Validation error', 400, ['errors' => $validator->errors()]);
        }

        $uid = (string) $request->user()->getKey();
        $reg = Registration::where('_id', $id)->where('user_id', $uid)->first();
        if (! $reg) {
            return $this->fail('Pendaftaran tidak ditemukan', 404);
        }
        if ($message = $this->blocker($reg)) {
            return $this->fail($message, 409);
        }

        $recipient = User::where('email', strtolower($request->input('recipient_email')))->first();
        if (! $recipient) {
            return $this->fail('Penerima belum terdaftar', 404);
        }
        $toId = (string) $recipient->getKey();
        if ($toId === $uid) {
            return $this->fail('Tidak dapat memindahkan BIB ke diri sendiri', 400);
        }
        if ($this->hasActiveRegistration($toId, (string) $reg->event_id)) {
            return $this->fail('Penerima sudah terdaftar pada event ini', 409);
        }
        if (BibTransfer::where('registration_id', $id)->where('status', 'requested')->exists()) {
            return $this->fail('Sudah ada pengajuan transfer yang menunggu persetujuan', 409);
        }

        try {
            $transfer = BibTransfer::create([
                'registration_id' => $id,
                'from_user_id' => $uid,
                'to_user_id' => $toId,
                'status' => 'requested',
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'E11000')) {
                return $this->fail('Sudah ada pengajuan transfer yang menunggu persetujuan', 409);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan transfer dikirim',
            'data' => ['id' => (string) $transfer->getKey(), 'status' => $transfer->status],
        ], 201);
    }

    // PATCH /transfers/{id}  { "action": "accept" | "reject" | "cancel" }
    public function respond(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), ['action' => 'required|in:accept,reject,cancel']);
        if ($validator->fails()) {
            return $this->fail('Validation error', 400, ['errors' => $validator->errors()]);
        }

        $transfer = BibTransfer::find($id);
        if (! $transfer) {
            return $this->fail('Transfer tidak ditemukan', 404);
        }

        $uid = (string) $request->user()->getKey();
        $action = $request->input('action');
        $allowed = $action === 'cancel'
            ? (string) $transfer->from_user_id === $uid
            : (string) $transfer->to_user_id === $uid;
        if (! $allowed) {
            return $this->fail('Forbidden', 403);
        }
        if ($transfer->status !== 'requested') {
            return $this->fail('Transfer sudah diproses', 409);
        }

        if ($action !== 'accept') {
            $newStatus = $action === 'reject' ? 'rejected' : 'cancelled';
            $changed = BibTransfer::where('_id', $transfer->getKey())->where('status', 'requested')
                ->update(['status' => $newStatus, 'respondedAt' => now()]);
            if (! $changed) {
                return $this->fail('Transfer sudah diproses', 409);
            }

            return response()->json([
                'success' => true,
                'message' => $action === 'reject' ? 'Transfer ditolak' : 'Transfer dibatalkan',
                'data' => ['id' => (string) $transfer->getKey(), 'status' => $newStatus],
            ]);
        }

        // ---- accept ----
        $reg = Registration::find($transfer->registration_id);
        if (! $reg || (string) $reg->user_id !== (string) $transfer->from_user_id) {
            return $this->fail('Pendaftaran sudah tidak dimiliki pengirim', 409);
        }
        if ($message = $this->blocker($reg)) {
            return $this->fail($message, 409);
        }
        if (! $this->profileComplete($uid)) {
            return $this->fail('Lengkapi profil Anda sebelum menerima transfer', 400);
        }
        if ($this->hasActiveRegistration($uid, (string) $reg->event_id)) {
            return $this->fail('Anda sudah terdaftar pada event ini', 409);
        }

        // Klaim atomik: hanya satu persetujuan yang berhasil
        $claimed = BibTransfer::where('_id', $transfer->getKey())->where('status', 'requested')
            ->update(['status' => 'accepted', 'respondedAt' => now()]);
        if (! $claimed) {
            return $this->fail('Transfer sudah diproses', 409);
        }

        try {
            $reg->update(['user_id' => $uid]);
            $reg->push('history', [
                'action' => 'bib_transfer',
                'detail' => ['from' => (string) $transfer->from_user_id, 'to' => $uid],
                'at' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            BibTransfer::where('_id', $transfer->getKey())->update(['status' => 'requested', 'respondedAt' => null]);
            if (str_contains($e->getMessage(), 'E11000')) {
                return $this->fail('Anda sudah terdaftar pada event ini', 409);
            }
            throw $e;
        }

        // BIB tetap sama, QR dibuat baru agar QR lama pengirim tidak berlaku
        Ticket::where('registration_id', (string) $reg->getKey())->update(['qrToken' => Str::random(40)]);

        return response()->json([
            'success' => true,
            'message' => 'Transfer disetujui',
            'data' => ['id' => (string) $transfer->getKey(), 'status' => 'accepted'],
        ]);
    }
}