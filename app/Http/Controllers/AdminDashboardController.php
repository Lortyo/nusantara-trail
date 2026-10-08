<?php

namespace App\Http\Controllers;

use App\Models\CategoryChange;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    // event yang sedang dilihat: dari ?event=, atau event open terbaru
    private function pickEvent(Request $request): array
    {
        $events = RaceEvent::orderBy('eventDate', 'desc')->get();
        $wanted = $request->query('event');

        $event = $events->first(fn ($e) => (string) $e->getKey() === $wanted)
            ?? $events->first(fn ($e) => $e->status === 'open')
            ?? $events->first();

        return [$events, $event];
    }

    public function index(Request $request)
    {
        [$events, $event] = $this->pickEvent($request);
        if (! $event) return view('admin.dashboard', ['event' => null, 'events' => $events]);

        $eid = (string) $event->getKey();
        $cats = RaceCategory::where('event_id', $eid)->orderBy('distanceKm')->get();
        $regs = Registration::where('event_id', $eid)->whereIn('status', ['pending', 'paid'])->get();

        $paid = $regs->where('status', 'paid');
        $pending = $regs->where('status', 'pending')->filter(fn ($r) => now()->lt($r->expiresAt))->count();

        // pendapatan = pembayaran settlement (pendaftaran + biaya ganti kategori)
        $paidIds = $paid->map(fn ($r) => (string) $r->getKey())->values()->all();
        $changeIds = CategoryChange::where('event_id', $eid)->where('status', 'completed')->get()
            ->map(fn ($c) => (string) $c->getKey())->values()->all();
        $revenue = (int) Payment::where('status', 'settlement')
            ->where(function ($q) use ($paidIds, $changeIds) {
                $q->whereIn('registration_id', $paidIds)->orWhereIn('change_id', $changeIds);
            })->sum('amount');

        $quota = (int) $cats->sum('quota');
        $left = (int) $cats->sum('slotsAvailable');
        $kits = Ticket::where('event_id', $eid)->whereNotNull('kitCollectedAt')->count();

        $stats = [
            'total' => $paid->count() + $pending,
            'week' => $regs->filter(fn ($r) => $r->created_at && $r->created_at->gte(now()->subDays(7)))->count(),
            'revenue' => $revenue,
            'paid' => $paid->count(),
            'pending' => $pending,
            'left' => $left,
            'quota' => $quota,
            'filledPct' => $quota > 0 ? (int) round(($quota - $left) / $quota * 100) : 0,
            'kits' => $kits,
            'kitsPct' => $paid->count() > 0 ? (int) round($kits / $paid->count() * 100) : 0,
        ];

        $catRows = $cats->map(fn ($c) => [
            'label' => $this->km($c->distanceKm) . 'K · ' . $c->name,
            'taken' => $c->quota - $c->slotsAvailable,
            'quota' => $c->quota,
            'left' => $c->slotsAvailable,
            'pct' => $c->quota > 0 ? (int) round(($c->quota - $c->slotsAvailable) / $c->quota * 100) : 0,
        ]);

        $qPending = Qualification::where('event_id', $eid)->where('status', 'pending')->orderBy('created_at')->get();
        $qual = [
            'count' => $qPending->count(),
            'oldestDays' => $qPending->isNotEmpty() && $qPending->first()->created_at
                ? (int) $qPending->first()->created_at->diffInDays(now()) : 0,
        ];

        $recent = Registration::where('event_id', $eid)->whereIn('status', ['pending', 'paid'])
            ->orderBy('created_at', 'desc')->paginate(4)->withQueryString();

        $rows = $recent->map(function ($r) use ($cats) {
            $u = User::find($r->user_id);
            $cat = $cats->first(fn ($c) => (string) $c->getKey() === (string) $r->category_id);
            $t = Ticket::where('registration_id', (string) $r->getKey())->first();

            return [
                'name' => optional($u)->name ?? '-',
                'email' => optional($u)->email,
                'bib' => optional($t)->bibNumber ?? '—',
                'category' => $cat ? $this->km($cat->distanceKm) . 'K · ' . $cat->name : '-',
                'registered' => optional($r->created_at)->format('d M, H:i'),
                'paid' => $r->status === 'paid',
                'kit' => optional($t)->kitCollectedAt !== null,
            ];
        });

        return view('admin.dashboard', compact('events', 'event', 'stats', 'catRows', 'qual', 'recent', 'rows'));
    }

    public function export(Request $request)
    {
        [, $event] = $this->pickEvent($request);
        abort_if(! $event, 404);

        $eid = (string) $event->getKey();
        $cats = RaceCategory::where('event_id', $eid)->get()->keyBy(fn ($c) => (string) $c->getKey());
        $filename = Str::slug($event->name) . '-registrations-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($eid, $cats) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Registration code', 'Name', 'Email', 'Phone', 'Category', 'Jersey', 'BIB', 'Status', 'Amount (IDR)', 'Registered at', 'Kit collected at']);

            foreach (Registration::where('event_id', $eid)->orderBy('created_at')->get() as $r) {
                $u = User::find($r->user_id);
                $p = Profile::where('user_id', (string) $r->user_id)->first();
                $t = Ticket::where('registration_id', (string) $r->getKey())->first();
                $c = $cats[(string) $r->category_id] ?? null;

                fputcsv($out, [
                    $r->registrationCode, optional($u)->name, optional($u)->email, optional($p)->phone,
                    $c ? $this->km($c->distanceKm) . 'K ' . $c->name : '', $r->jerseySize,
                    optional($t)->bibNumber, $r->status, $r->totalAmount,
                    optional($r->created_at)->format('Y-m-d H:i'), optional(optional($t)->kitCollectedAt)->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}