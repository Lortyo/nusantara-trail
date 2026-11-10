<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Result;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResultController extends Controller
{
    use ParticipantHelpers;

    private function fail(string $message, int $status, array $extra = [])
    {
        return response()->json(array_merge(['success' => false, 'message' => $message], $extra), $status);
    }

    private function toSeconds(?string $time): ?int
    {
        if (blank($time)) {
            return null;
        }
        [$h, $m, $s] = array_map('intval', explode(':', $time));

        return $h * 3600 + $m * 60 + $s;
    }

    private function normalizeTime(string $time): string
    {
        [$h, $m, $s] = array_map('intval', explode(':', $time));

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    // Hitung ulang peringkat keseluruhan dan per kategori dari waktu finish
    private function recalculateRanks(string $eventId): void
    {
        $regs = Registration::where('event_id', $eventId)->get()->keyBy(fn ($r) => (string) $r->getKey());
        $results = Result::whereIn('registration_id', $regs->keys()->all())->get();

        $finished = $results->filter(fn ($r) => $r->status === 'finished' && $r->finishTime)
            ->sortBy(fn ($r) => $this->toSeconds($r->finishTime))->values();

        $perCategory = [];
        $ranks = [];
        foreach ($finished as $i => $res) {
            $cat = (string) $regs[(string) $res->registration_id]->category_id;
            $perCategory[$cat] = ($perCategory[$cat] ?? 0) + 1;
            $ranks[(string) $res->getKey()] = ['overallRank' => $i + 1, 'categoryRank' => $perCategory[$cat]];
        }

        foreach ($results as $res) {
            $res->update($ranks[(string) $res->getKey()] ?? ['overallRank' => null, 'categoryRank' => null]);
        }
    }

    // GET /events/{eventId}/results  (publik)
    public function index(Request $request, string $eventId)
    {
        $event = RaceEvent::find($eventId);
        if (! $event || $event->status === 'draft') {
            return $this->fail('Event tidak ditemukan', 404);
        }

        $limit = min(max((int) $request->query('limit', 50), 1), 200);
        $pageNo = max((int) $request->query('page', 1), 1);

        $regQuery = Registration::where('event_id', $eventId)->where('status', 'paid');
        $category = $request->query('category_id');
        if (is_string($category) && $category !== '') {
            $regQuery->where('category_id', $category);
        }
        $regs = $regQuery->get()->keyBy(fn ($r) => (string) $r->getKey());

        $all = Result::whereIn('registration_id', $regs->keys()->all())->get()
            ->sortBy(fn ($r) => [$r->overallRank === null ? 1 : 0, $r->overallRank ?? 0])->values();

        $tickets = Ticket::whereIn('registration_id', $regs->keys()->all())->get()->keyBy(fn ($t) => (string) $t->registration_id);
        $profiles = Profile::whereIn('user_id', $regs->pluck('user_id')->map(fn ($v) => (string) $v)->all())->get()->keyBy(fn ($p) => (string) $p->user_id);
        $cats = RaceCategory::whereIn('_id', $regs->pluck('category_id')->unique()->all())->get()->keyBy(fn ($c) => (string) $c->getKey());

        $data = $all->forPage($pageNo, $limit)->map(function ($res) use ($regs, $tickets, $profiles, $cats) {
            $reg = $regs[(string) $res->registration_id];
            $cat = $cats[(string) $reg->category_id] ?? null;

            return [
                'id' => (string) $res->getKey(),
                'overallRank' => $res->overallRank,
                'categoryRank' => $res->categoryRank,
                'bibNumber' => optional($tickets[(string) $res->registration_id] ?? null)->bibNumber,
                'bibName' => optional($profiles[(string) $reg->user_id] ?? null)->bibName,
                'category' => $cat ? $this->km($cat->distanceKm) . 'K' : null,
                'finishTime' => $res->finishTime,
                'status' => $res->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
            'meta' => [
                'page' => $pageNo,
                'limit' => $limit,
                'total' => $all->count(),
                'last_page' => max((int) ceil($all->count() / $limit), 1),
            ],
        ]);
    }

    // POST /organizer/events/{eventId}/results  (input atau import, boleh banyak sekaligus)
    public function store(Request $request, string $eventId)
    {
        if (! RaceEvent::where('_id', $eventId)->exists()) {
            return $this->fail('Event tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'results' => 'required|array|min:1|max:500',
            'results.*.registration_id' => 'required|string|distinct',
            'results.*.finishTime' => ['nullable', 'string', 'regex:/^\d{1,2}:[0-5]\d:[0-5]\d$/'],
            'results.*.status' => 'nullable|in:finished,dnf,dns,dsq',
        ]);
        if ($validator->fails()) {
            return $this->fail('Validation error', 400, ['errors' => $validator->errors()]);
        }

        $rows = $validator->validated()['results'];
        $valid = Registration::where('event_id', $eventId)->where('status', 'paid')
            ->whereIn('_id', array_column($rows, 'registration_id'))->get()
            ->keyBy(fn ($r) => (string) $r->getKey());

        $errors = [];
        foreach ($rows as $i => $row) {
            $status = $row['status'] ?? 'finished';
            if (! isset($valid[$row['registration_id']])) {
                $errors["results.$i.registration_id"] = 'Pendaftaran tidak ditemukan pada event ini atau belum lunas';
            } elseif ($status === 'finished' && blank($row['finishTime'] ?? null)) {
                $errors["results.$i.finishTime"] = 'finishTime wajib untuk status finished';
            }
        }
        if ($errors) {
            return $this->fail('Validation error', 400, ['errors' => $errors]);
        }

        $inserted = 0;
        $updated = 0;
        foreach ($rows as $row) {
            $status = $row['status'] ?? 'finished';
            $payload = [
                'status' => $status,
                'finishTime' => $status === 'finished' ? $this->normalizeTime($row['finishTime']) : null,
            ];

            $existing = Result::where('registration_id', $row['registration_id'])->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Result::create($payload + ['registration_id' => $row['registration_id'], 'overallRank' => null, 'categoryRank' => null]);
                $inserted++;
            }
        }

        $this->recalculateRanks($eventId);

        return response()->json([
            'success' => true,
            'message' => 'Hasil lomba disimpan',
            'data' => ['inserted' => $inserted, 'updated' => $updated],
        ], 201);
    }

    // PUT /organizer/results/{id}
    public function update(Request $request, string $id)
    {
        $result = Result::find($id);
        if (! $result) {
            return $this->fail('Hasil tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'finishTime' => ['nullable', 'string', 'regex:/^\d{1,2}:[0-5]\d:[0-5]\d$/'],
            'status' => 'sometimes|in:finished,dnf,dns,dsq',
        ]);
        if ($validator->fails()) {
            return $this->fail('Validation error', 400, ['errors' => $validator->errors()]);
        }

        $d = $validator->validated();
        $status = $d['status'] ?? $result->status;
        $time = array_key_exists('finishTime', $d) ? $d['finishTime'] : $result->finishTime;

        if ($status === 'finished' && blank($time)) {
            return $this->fail('finishTime wajib untuk status finished', 400);
        }

        $result->update([
            'status' => $status,
            'finishTime' => $status === 'finished' ? $this->normalizeTime($time) : null,
        ]);

        $reg = Registration::find($result->registration_id);
        if ($reg) {
            $this->recalculateRanks((string) $reg->event_id);
        }
        $result = $result->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Hasil diperbarui',
            'data' => [
                'id' => (string) $result->getKey(),
                'finishTime' => $result->finishTime,
                'status' => $result->status,
                'overallRank' => $result->overallRank,
                'categoryRank' => $result->categoryRank,
            ],
        ]);
    }
}