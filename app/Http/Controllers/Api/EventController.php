<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Models\Profile;
use App\Models\Registration;
use App\Models\Ticket;

final class EventController extends Controller
{
    use ParticipantHelpers;
    public function entryList(Request $request, string $id)
    {
        $event = RaceEvent::find($id);
        if (! $event || $event->status === 'draft') {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $limit = min(max((int) $request->query('limit', 50), 1), 200);
        $q = Ticket::where('event_id', $id);

        $category = $request->query('category_id');
        if (is_string($category) && $category !== '') {
            $ids = Registration::where('event_id', $id)->where('category_id', $category)->where('status', 'paid')
                ->get()->map(fn ($r) => (string) $r->getKey())->all();
            $q->whereIn('registration_id', $ids);
        }

        $page = $q->orderBy('issuedAt')->paginate($limit);
        $tickets = $page->getCollection();

        $regs = Registration::whereIn('_id', $tickets->pluck('registration_id')->all())->get()
            ->keyBy(fn ($r) => (string) $r->getKey());
        $profiles = Profile::whereIn('user_id', $regs->pluck('user_id')->map(fn ($v) => (string) $v)->all())->get()
            ->keyBy(fn ($p) => (string) $p->user_id);
        $cats = RaceCategory::whereIn('_id', $regs->pluck('category_id')->unique()->all())->get()
            ->keyBy(fn ($c) => (string) $c->getKey());

        $data = $tickets->map(function ($t) use ($regs, $profiles, $cats) {
            $r = $regs[(string) $t->registration_id] ?? null;
            $c = $r ? ($cats[(string) $r->category_id] ?? null) : null;

            return [
                'bibNumber' => $t->bibNumber,
                'bibName' => $r ? optional($profiles[(string) $r->user_id] ?? null)->bibName : null,
                'category' => $c ? $this->km($c->distanceKm) . 'K' : null,
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
    public function index(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 50);

        $sortable = ['eventDate', 'name', 'createdAt'];
        $raw = $request->query('sort');
        $raw = is_string($raw) ? $raw : 'eventDate';
        $dir = str_starts_with($raw, '-') ? 'desc' : 'asc';
        $field = ltrim($raw, '-');
        if (! in_array($field, $sortable, true)) {
            $field = 'eventDate';
        }

        $q = RaceEvent::where('status', '!=', 'draft');

        // Parameter hanya dipakai jika berupa string (menolak ?q[$ne]=x)
        $s = $request->query('q');
        if (is_string($s) && trim($s) !== '') {
            $q->where('name', 'like', '%' . trim($s) . '%');
        }

        $status = $request->query('status');
        if (is_string($status) && $status !== '') {
            $q->where('status', $status);
        }

        if ($from = $this->parseDate($request->query('date_from'))) {
            $q->where('eventDate', '>=', $from->startOfDay());
        }
        if ($to = $this->parseDate($request->query('date_to'))) {
            $q->where('eventDate', '<=', $to->endOfDay());
        }

        $page = $q->orderBy($field, $dir)->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $page->items(),
            'meta' => [
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    public function show(string $id)
    {
        $event = RaceEvent::find($id);
        if (! $event || $event->status === 'draft') {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $categories = RaceCategory::where('event_id', (string) $event->getKey())
            ->orderBy('distanceKm')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['event' => $event, 'categories' => $categories],
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function changeStatus(Request $request, string $id)
    {
        $event = RaceEvent::find($id);
        if (! $event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $action = (string) $request->input('action');
        if (! in_array($action, ['publish', 'reopen', 'close', 'finish'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'action harus salah satu dari: publish, reopen, close, finish',
            ], 400);
        }

        if (in_array($action, ['publish', 'reopen'], true)) {
            $hasCategories = RaceCategory::where('event_id', $id)->exists();
            if (! $hasCategories || blank($event->registrationOpenAt) || blank($event->registrationCloseAt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tambahkan minimal satu kategori dan isi periode pendaftaran sebelum membuka pendaftaran',
                ], 400);
            }
            $event->update(['status' => 'open']);
        } elseif ($action === 'close') {
            $event->update(['status' => 'closed']);
        } else {
            $event->update(['status' => 'finished']);
        }

        return response()->json(['success' => true, 'message' => 'Status event diperbarui', 'data' => $event->fresh()]);
    }
}