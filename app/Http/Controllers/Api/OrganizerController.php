<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CategoryChange;
use App\Models\Payment;
use App\Models\RaceEvent;
use Illuminate\Support\Facades\Validator;

class OrganizerController extends Controller
{
    use ParticipantHelpers;

    private function stringParam(Request $request, string $key): ?string
    {
        $v = $request->query($key);

        return is_string($v) && trim($v) !== '' ? trim($v) : null;
    }

    // GET /organizer/registrations
    public function registrations(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 100);

        $sortable = ['created_at' => 'created_at', 'createdAt' => 'created_at', 'totalAmount' => 'totalAmount', 'status' => 'status'];
        $raw = $this->stringParam($request, 'sort') ?? '-created_at';
        $dir = str_starts_with($raw, '-') ? 'desc' : 'asc';
        $field = $sortable[ltrim($raw, '-')] ?? 'created_at';

        $q = Registration::query();

        if ($v = $this->stringParam($request, 'event_id')) {
            $q->where('event_id', $v);
        }
        if ($v = $this->stringParam($request, 'category_id')) {
            $q->where('category_id', $v);
        }
        if ($v = $this->stringParam($request, 'status')) {
            $q->where('status', $v);
        }

        // Cari berdasarkan nama, email, nomor BIB, atau kode registrasi
        if ($s = $this->stringParam($request, 'q')) {
            $s = mb_substr($s, 0, 60);
            $userIds = Profile::where('fullName', 'like', '%' . $s . '%')->pluck('user_id')->map(fn ($v) => (string) $v)->all();
            $userIds = array_merge(
                $userIds,
                User::where('email', 'like', '%' . $s . '%')->get()->map(fn ($u) => (string) $u->getKey())->all()
            );
            $regIds = Ticket::where('bibNumber', 'like', '%' . $s . '%')->pluck('registration_id')->map(fn ($v) => (string) $v)->all();

            $q->where(function ($w) use ($userIds, $regIds, $s) {
                $w->whereIn('user_id', $userIds)
                    ->orWhereIn('_id', $regIds)
                    ->orWhere('registrationCode', 'like', '%' . $s . '%');
            });
        }

        $page = $q->orderBy($field, $dir)->paginate($limit);
        $regs = $page->getCollection();

        $uids = $regs->pluck('user_id')->map(fn ($v) => (string) $v)->unique()->all();
        $users = User::whereIn('_id', $uids)->get()->keyBy(fn ($u) => (string) $u->getKey());
        $profiles = Profile::whereIn('user_id', $uids)->get()->keyBy(fn ($p) => (string) $p->user_id);
        $tickets = Ticket::whereIn('registration_id', $regs->map(fn ($r) => (string) $r->getKey())->all())->get()
            ->keyBy(fn ($t) => (string) $t->registration_id);
        $cats = RaceCategory::whereIn('_id', $regs->pluck('category_id')->unique()->all())->get()
            ->keyBy(fn ($c) => (string) $c->getKey());

        $data = $regs->map(function ($r) use ($users, $profiles, $tickets, $cats) {
            $id = (string) $r->getKey();
            $u = $users[(string) $r->user_id] ?? null;
            $p = $profiles[(string) $r->user_id] ?? null;
            $t = $tickets[$id] ?? null;
            $c = $cats[(string) $r->category_id] ?? null;

            return [
                'id' => $id,
                'registrationCode' => $r->registrationCode,
                'name' => optional($p)->fullName ?? optional($u)->name,
                'email' => optional($u)->email,
                'bibNumber' => optional($t)->bibNumber,
                'category' => $c ? $this->km($c->distanceKm) . 'K ' . $c->name : null,
                'category_id' => (string) $r->category_id,
                'jerseySize' => $r->jerseySize,
                'status' => $r->status,
                'totalAmount' => (int) $r->totalAmount,
                'kitCollectedAt' => optional(optional($t)->kitCollectedAt)->toIso8601String(),
                'checkedInAt' => optional(optional($t)->checkedInAt)->toIso8601String(),
                'createdAt' => optional($r->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
            'meta' => [
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

        private function fail(string $message, int $status, array $extra = [])
    {
        return response()->json(array_merge(['success' => false, 'message' => $message], $extra), $status);
    }

    // POST /organizer/checkin/scan
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qrToken' => 'required_without:bibNumber|nullable|string|max:100',
            'bibNumber' => 'required_without:qrToken|nullable|string|max:20',
            'event_id' => 'required_with:bibNumber|nullable|string',
            'mode' => 'nullable|in:kit,checkin',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $d = $validator->validated();
        $isKit = ($d['mode'] ?? 'kit') === 'kit';

        // QR token, atau nomor BIB manual sebagai cadangan di race day
        $ticket = ! empty($d['qrToken'])
            ? Ticket::where('qrToken', $d['qrToken'])->first()
            : Ticket::where('event_id', $d['event_id'])->where('bibNumber', $d['bibNumber'])->first();

        if (! $ticket) {
            return $this->fail('QR Code atau nomor BIB tidak valid', 404);
        }
        if (! empty($d['event_id']) && (string) $ticket->event_id !== $d['event_id']) {
            return $this->fail('Peserta terdaftar pada event lain', 404);
        }

        $reg = Registration::find($ticket->registration_id);
        if (! $reg || $reg->status !== 'paid') {
            return $this->fail('Pendaftaran belum lunas', 409);
        }

        $profile = Profile::where('user_id', (string) $reg->user_id)->first();
        $name = optional($profile)->fullName ?? optional(User::find($reg->user_id))->name;

        if (! $isKit && ! $ticket->kitCollectedAt) {
            return $this->fail('Race kit belum diambil. Ambil race kit dulu sebelum check-in', 409);
        }

        [$at, $by] = $isKit ? ['kitCollectedAt', 'kitCollectedBy'] : ['checkedInAt', 'checkedInBy'];
        $staff = $request->user()->name;

        // Atomik: hanya satu scan yang berhasil mengisi waktu (QR hanya bisa dipakai sekali)
        $changed = Ticket::where('_id', $ticket->getKey())->whereNull($at)->update([$at => now(), $by => $staff]);
        $fresh = $ticket->fresh();

        $data = [
            'bibNumber' => $fresh->bibNumber,
            'name' => $name,
            'mode' => $isKit ? 'kit' : 'checkin',
            'recordedAt' => optional($fresh->{$at})->toIso8601String(),
            'recordedBy' => $fresh->{$by},
        ];

        if (! $changed) {
            return $this->fail('QR Code sudah digunakan', 409, ['data' => $data]);
        }

        return response()->json([
            'success' => true,
            'message' => $isKit ? 'Race kit tercatat' : 'Check-in berhasil',
            'data' => $data,
        ]);
    }

    // GET /organizer/dashboard/summary
    public function dashboardSummary(Request $request)
    {
        $wanted = $this->stringParam($request, 'event_id');

        $event = $wanted
            ? RaceEvent::find($wanted)
            : (RaceEvent::where('status', 'open')->orderBy('eventDate', 'desc')->first()
                ?? RaceEvent::orderBy('eventDate', 'desc')->first());

        if (! $event) {
            return $this->fail('Event tidak ditemukan', 404);
        }

        $eid = (string) $event->getKey();
        $cats = RaceCategory::where('event_id', $eid)->orderBy('distanceKm')->get();
        $regs = Registration::where('event_id', $eid)->whereIn('status', ['pending', 'paid'])->get();

        $paid = $regs->where('status', 'paid');
        $pending = $regs->where('status', 'pending')->filter(fn ($r) => now()->lt($r->expiresAt));

        $paidIds = $paid->map(fn ($r) => (string) $r->getKey())->values()->all();
        $changeIds = CategoryChange::where('event_id', $eid)->where('status', 'completed')->get()
            ->map(fn ($c) => (string) $c->getKey())->values()->all();
        $revenue = (int) Payment::where('status', 'settlement')
            ->where(function ($q) use ($paidIds, $changeIds) {
                $q->whereIn('registration_id', $paidIds)->orWhereIn('change_id', $changeIds);
            })->sum('amount');

        $categories = $cats->map(function ($c) use ($paid, $regs) {
        $id = (string) $c->getKey();

        return [
            'id' => $id,
            'name' => $this->km($c->distanceKm) . 'K ' . $c->name,
            'quota' => (int) $c->quota,
            'registered' => $regs->where('category_id', $id)->count(),
            'paid' => $paid->where('category_id', $id)->count(),
            'slotsAvailable' => (int) $c->slotsAvailable,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'event' => ['id' => $eid, 'name' => $event->name, 'status' => $event->status],
                'totalRegistrations' => $paid->count() + $pending->count(),
                'paid' => $paid->count(),
                'pending' => $pending->count(),
                'quota' => (int) $cats->sum('quota'),
                'quotaRemaining' => (int) $cats->sum('slotsAvailable'),
                'revenue' => $revenue,
                'kitCollected' => Ticket::where('event_id', $eid)->whereNotNull('kitCollectedAt')->count(),
                'checkedIn' => Ticket::where('event_id', $eid)->whereNotNull('checkedInAt')->count(),
                'categories' => $categories,
            ],
        ]);
    }
}