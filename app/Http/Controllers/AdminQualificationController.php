<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PicksEvent;
use App\Models\Profile;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminQualificationController extends Controller
{
    use PicksEvent;

    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    public function index(Request $request)
    {
        [$events, $event] = $this->pickEvent($request);
        if (! $event) return view('admin.qualifications', ['event' => null, 'events' => $events]);

        $eid = (string) $event->getKey();
        $cats = RaceCategory::where('event_id', $eid)->orderBy('distanceKm')->get()->keyBy(fn ($c) => (string) $c->getKey());
        $status = $request->query('status', 'pending');
        $catId = $request->query('category');
        $s = trim((string) $request->query('q'));

        $page = Qualification::where('event_id', $eid)
            ->when(in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('status', $status))
            ->when($catId, fn ($q, $v) => $q->where('category_id', $v))
            ->when($s !== '', function ($q) use ($s) {
                $uids = User::where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->get()
                    ->map(fn ($u) => (string) $u->getKey())->all();
                $q->where(function ($w) use ($s, $uids) {
                    $w->where('requestCode', 'like', "%{$s}%")->orWhereIn('user_id', $uids);
                });
            })
            ->orderBy('created_at', $status === 'pending' ? 'asc' : 'desc')
            ->paginate(6)->withQueryString();

        $uids = $page->map(fn ($q) => (string) $q->user_id)->unique()->values()->all();
        $users = User::whereIn('_id', $uids)->get()->keyBy(fn ($u) => (string) $u->getKey());
        $profiles = Profile::whereIn('user_id', $uids)->get()->keyBy('user_id');
        $regs = Registration::where('event_id', $eid)->whereIn('user_id', $uids)->whereIn('status', ['pending', 'paid'])->get()->keyBy('user_id');
        $tickets = Ticket::where('event_id', $eid)->get()->keyBy('registration_id');
        $disk = Storage::disk('public');

        $rows = $page->map(function ($q) use ($users, $profiles, $cats, $regs, $tickets, $event, $disk) {
            $u = $users[(string) $q->user_id] ?? null;
            $p = $profiles[(string) $q->user_id] ?? null;
            $cat = $cats[(string) $q->category_id] ?? null;
            $min = data_get($cat, 'qualification.minDistanceKm');
            $years = (int) data_get($cat, 'qualification.withinYears');
            $reg = $regs[(string) $q->user_id] ?? null;
            $bib = $reg ? optional($tickets[(string) $reg->getKey()] ?? null)->bibNumber : null;
            $cutoff = $years ? ($event->eventDate ?? now())->copy()->subYears($years)->format('d M Y') : null;

            return [
                'id' => (string) $q->getKey(),
                'code' => $q->requestCode,
                'submitted' => optional($q->created_at)->format('d M Y'),
                'submittedShort' => optional($q->created_at)->format('d M, H:i'),
                'runner' => optional($p)->fullName ?? optional($u)->name ?? '-',
                'bib' => $bib ?? '—',
                'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : '-',
                'status' => $q->status,
                'note' => $q->reviewNote,
                'requirement' => $min ? $this->km($min) . ' km within the last ' . $years . ' years' : '-',
                'cutoff' => $cutoff,
                'race' => $q->raceName,
                'raceDate' => optional($q->raceDate)->format('d M Y'),
                'distance' => $this->km($q->distanceKm),
                'finish' => $q->finishTime,
                'raceBib' => $q->raceBib ?: '—',
                'resultUrl' => $q->resultUrl,
                'proofUrl' => $q->proofPath ? $disk->url($q->proofPath) : null,
                'proofName' => $q->proofName,
                'proofSize' => ($q->proofPath && $disk->exists($q->proofPath)) ? round($disk->size($q->proofPath) / 1024) . ' KB' : '',
                'proofIsImage' => (bool) preg_match('/\.(jpe?g|png)$/i', (string) $q->proofPath),
            ];
        })->all();

        $counts = [
            'pending' => Qualification::where('event_id', $eid)->where('status', 'pending')->count(),
            'approved' => Qualification::where('event_id', $eid)->where('status', 'approved')->count(),
            'rejected' => Qualification::where('event_id', $eid)->where('status', 'rejected')->count(),
        ];
        $oldest = Qualification::where('event_id', $eid)->where('status', 'pending')->orderBy('created_at')->first();
        $oldestDays = $oldest && $oldest->created_at ? (int) $oldest->created_at->diffInDays(now()) : null;
        $qualCats = $cats->filter(fn ($c) => data_get($c, 'qualification.required'));

        return view('admin.qualifications', compact('events', 'event', 'page', 'rows', 'counts', 'oldestDays', 'qualCats', 'status'));
    }

    public function review(Request $request, string $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'required_if:decision,reject|nullable|string|max:500',
        ], ['note.required_if' => 'A review note is required when rejecting.']);

        $q = Qualification::findOrFail($id);
        $status = $data['decision'] === 'approve' ? 'approved' : 'rejected';

        $changed = Qualification::where('_id', $q->getKey())->where('status', 'pending')->update([
            'status' => $status,
            'reviewNote' => $data['note'] ?? null,
            'reviewedBy' => (string) $request->user()->getKey(),
            'reviewedAt' => now(),
        ]);
        if (! $changed) return back()->withErrors(['review' => 'This request was already reviewed.']);

        $user = User::find($q->user_id);
        $cat = RaceCategory::find($q->category_id);
        if ($user) {
            $label = ($cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : 'your category');
            $text = $status === 'approved'
                ? "Hi {$user->name},\n\nYour qualification ({$q->requestCode}) for {$label} has been approved. You can now register for this category.\n\nNusantara Trail Series"
                : "Hi {$user->name},\n\nYour qualification ({$q->requestCode}) for {$label} was not approved.\nReason: {$data['note']}\n\nYou may submit a new proof from the participant portal.\n\nNusantara Trail Series";
            try {
                Mail::raw($text, fn ($m) => $m->to($user->email)->subject('Qualification ' . $status . ' · ' . $q->requestCode));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('admin.qualifications.index', ['event' => (string) $q->event_id, 'next' => 1])
            ->with('success', 'Qualification ' . $status . '. The runner has been notified.');
    }
}