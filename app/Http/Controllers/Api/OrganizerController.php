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
}