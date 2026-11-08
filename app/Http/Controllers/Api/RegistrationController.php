<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class RegistrationController extends Controller
{
    use ParticipantHelpers;

    private const HOLD_MINUTES = 15;

    private const REQUIRED_PROFILE_FIELDS = [
        'fullName', 'bibName', 'gender', 'dateOfBirth', 'nationality', 'phone',
        'address', 'city', 'country', 'identity_type', 'identity_number',
        'emergency_name', 'emergency_phone', 'emergency_relationship',
    ];

    public function __construct(private MidtransService $midtrans) {}

    private function fail(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    private function profileComplete(?Profile $profile): bool
    {
        if (! $profile) {
            return false;
        }
        foreach (self::REQUIRED_PROFILE_FIELDS as $field) {
            if (blank($profile->{$field})) {
                return false;
            }
        }

        return true;
    }

    // POST /registrations  (war slot)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string',
            'category_id' => 'required|string',
            'jerseySize' => 'required|in:XS,S,M,L,XL,XXL',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $data = $validator->validated();
        $uid = (string) $request->user()->getKey();

        $event = RaceEvent::find($data['event_id']);
        if (! $event) {
            return $this->fail('Event tidak ditemukan', 404);
        }
        $category = RaceCategory::where('_id', $data['category_id'])->where('event_id', $data['event_id'])->first();
        if (! $category) {
            return $this->fail('Kategori tidak ditemukan', 404);
        }

        $open = $event->status === 'open'
            && $event->registrationOpenAt && now()->gte($event->registrationOpenAt)
            && $event->registrationCloseAt && now()->lte($event->registrationCloseAt);
        if (! $open) {
            return $this->fail('Pendaftaran belum dibuka atau sudah ditutup', 409);
        }

        if (! $this->profileComplete(Profile::where('user_id', $uid)->first())) {
            return $this->fail('Lengkapi profil sebelum mendaftar', 400);
        }

        $already = Registration::where('user_id', $uid)->where('event_id', $data['event_id'])
            ->whereIn('status', ['pending', 'paid'])->exists();
        if ($already) {
            return $this->fail('Anda sudah terdaftar pada event ini', 409);
        }

        if (data_get($category, 'qualification.required')) {
            $approved = Qualification::where('user_id', $uid)
                ->where('category_id', (string) $category->getKey())
                ->where('status', 'approved')->exists();
            if (! $approved) {
                return $this->fail('Kategori ini membutuhkan kualifikasi yang sudah disetujui', 403);
            }
        }

        // Pengurangan kuota atomik: hanya berhasil jika slotsAvailable masih > 0
        $took = RaceCategory::where('_id', $category->getKey())->where('slotsAvailable', '>', 0)->decrement('slotsAvailable');
        if (! $took) {
            return $this->fail('Kuota habis', 409);
        }

        try {
            $reg = Registration::create([
                'user_id' => $uid,
                'event_id' => $data['event_id'],
                'category_id' => (string) $category->getKey(),
                'registrationCode' => 'NTS-' . now()->format('y') . '-' . strtoupper(Str::random(6)),
                'jerseySize' => $data['jerseySize'],
                'status' => 'pending',
                'totalAmount' => $this->priceFor($category, $this->isLocal($uid)),
                'expiresAt' => now()->addMinutes(self::HOLD_MINUTES),
            ]);
        } catch (\Throwable $e) {
            RaceCategory::where('_id', $category->getKey())->increment('slotsAvailable');

            // Indeks unik (user_id, event_id) menolak pendaftaran ganda yang bersamaan
            if (str_contains($e->getMessage(), 'E11000')) {
                return $this->fail('Anda sudah terdaftar pada event ini', 409);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Slot berhasil direservasi',
            'data' => [
                'id' => (string) $reg->getKey(),
                'registrationCode' => $reg->registrationCode,
                'status' => $reg->status,
                'totalAmount' => (int) $reg->totalAmount,
                'expiresAt' => $reg->expiresAt->toIso8601String(),
            ],
        ], 201);
    }

    // GET /registrations/my
    public function my(Request $request)
    {
        $uid = (string) $request->user()->getKey();
        $regs = Registration::where('user_id', $uid)->orderBy('created_at', 'desc')->get();

        $events = RaceEvent::whereIn('_id', $regs->pluck('event_id')->unique()->all())->get()
            ->keyBy(fn ($e) => (string) $e->getKey());
        $cats = RaceCategory::whereIn('_id', $regs->pluck('category_id')->unique()->all())->get()
            ->keyBy(fn ($c) => (string) $c->getKey());
        $tickets = Ticket::whereIn('registration_id', $regs->map(fn ($r) => (string) $r->getKey())->all())->get()
            ->keyBy('registration_id');

        $data = $regs->map(function ($r) use ($events, $cats, $tickets) {
            $id = (string) $r->getKey();
            $cat = $cats[(string) $r->category_id] ?? null;

            return [
                'id' => $id,
                'registrationCode' => $r->registrationCode,
                'status' => $r->status,
                'event' => optional($events[(string) $r->event_id] ?? null)->name,
                'event_id' => (string) $r->event_id,
                'category' => $cat ? $this->km($cat->distanceKm) . 'K' : null,
                'totalAmount' => (int) $r->totalAmount,
                'expiresAt' => optional($r->expiresAt)->toIso8601String(),
                'bibNumber' => optional($tickets[$id] ?? null)->bibNumber,
            ];
        })->values();

        return response()->json(['success' => true, 'message' => 'OK', 'data' => $data]);
    }

    // GET /registrations/{id}  (pemilik atau admin)
    public function show(Request $request, string $id)
    {
        $reg = Registration::find($id);
        if (! $reg) {
            return $this->fail('Pendaftaran tidak ditemukan', 404);
        }

        $isOwner = (string) $reg->user_id === (string) $request->user()->getKey();
        if (! $isOwner && $request->user()->role !== 'admin') {
            return $this->fail('Forbidden', 403);
        }

        $cat = RaceCategory::find($reg->category_id);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'id' => (string) $reg->getKey(),
                'registrationCode' => $reg->registrationCode,
                'jerseySize' => $reg->jerseySize,
                'status' => $reg->status,
                'totalAmount' => (int) $reg->totalAmount,
                'expiresAt' => optional($reg->expiresAt)->toIso8601String(),
                'event' => optional(RaceEvent::find($reg->event_id))->name,
                'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : null,
            ],
        ]);
    }

    // DELETE /registrations/{id}  (batalkan reservasi yang belum dibayar)
    public function destroy(Request $request, string $id)
    {
        $reg = Registration::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->first();
        if (! $reg) {
            return $this->fail('Pendaftaran tidak ditemukan', 404);
        }
        if ($reg->status !== 'pending') {
            return $this->fail('Hanya reservasi yang belum dibayar yang dapat dibatalkan', 409);
        }

        $this->midtrans->releaseRegistration($reg, 'cancelled');
        Payment::where('registration_id', (string) $reg->getKey())->where('status', 'pending')->update(['status' => 'cancel']);

        return response()->json(['success' => true, 'message' => 'Reservasi dibatalkan, slot dikembalikan']);
    }

        private function qrDataUri(string $payload): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(320, 1), new SvgImageBackEnd()));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($payload));
    }

    // GET /registrations/{id}/credential  (hanya pemilik, hanya status paid)
    public function credential(Request $request, string $id)
    {
        $reg = Registration::find($id);
        if (! $reg) {
            return $this->fail('Pendaftaran tidak ditemukan', 404);
        }
        if ((string) $reg->user_id !== (string) $request->user()->getKey()) {
            return $this->fail('Forbidden', 403);
        }
        if ($reg->status !== 'paid') {
            return $this->fail('Pembayaran belum lunas', 403);
        }

        $ticket = Ticket::where('registration_id', (string) $reg->getKey())->first();
        if (! $ticket) {
            return $this->fail('Tiket belum tersedia', 404);
        }

        $profile = Profile::where('user_id', (string) $reg->user_id)->first();
        $cat = RaceCategory::find($reg->category_id);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'bibNumber' => $ticket->bibNumber,
                'bibName' => optional($profile)->bibName,
                'name' => optional($profile)->fullName,
                'event' => optional(RaceEvent::find($reg->event_id))->name,
                'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : null,
                'qrToken' => $ticket->qrToken,
                'qrImage' => $this->qrDataUri($ticket->qrToken),
                'kitCollectedAt' => optional($ticket->kitCollectedAt)->toIso8601String(),
                'checkedInAt' => optional($ticket->checkedInAt)->toIso8601String(),
                'issuedAt' => optional($ticket->issuedAt)->toIso8601String(),
            ],
        ]);
    }
}