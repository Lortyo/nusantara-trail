<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PicksEvent;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminParticipantController extends Controller
{
    use PicksEvent;

    private const COUNTRY = ['id' => 'Indonesia', 'my' => 'Malaysia'];
    private const PHONE = ['id' => '+62', 'my' => '+60'];

    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    private function rp($v): string
    {
        return 'Rp ' . number_format((float) $v, 0, ',', '.');
    }

    public function index(Request $request)
    {
        [$events, $event] = $this->pickEvent($request);
        if (! $event) return view('admin.participants', ['event' => null, 'events' => $events]);

        $eid = (string) $event->getKey();
        $cats = RaceCategory::where('event_id', $eid)->orderBy('distanceKm')->get()->keyBy(fn ($c) => (string) $c->getKey());

        $regs = Registration::where('event_id', $eid)->whereIn('status', ['pending', 'paid'])->orderBy('created_at', 'desc')->get()
            ->filter(fn ($r) => $r->status === 'paid' || now()->lt($r->expiresAt))->values();

        $uids = $regs->map(fn ($r) => (string) $r->user_id)->unique()->values()->all();
        $rids = $regs->map(fn ($r) => (string) $r->getKey())->values()->all();
        $users = User::whereIn('_id', $uids)->get()->keyBy(fn ($u) => (string) $u->getKey());
        $profiles = Profile::whereIn('user_id', $uids)->get()->keyBy('user_id');
        $tickets = Ticket::where('event_id', $eid)->get()->keyBy('registration_id');
        $payments = Payment::whereIn('registration_id', $rids)->where('status', 'settlement')->get()->keyBy('registration_id');
        $quals = Qualification::where('event_id', $eid)->where('status', 'approved')->get()->keyBy(fn ($q) => $q->user_id . '|' . $q->category_id);

        $all = $regs->map(function ($r) use ($users, $profiles, $tickets, $payments, $cats, $quals) {
            $u = $users[(string) $r->user_id] ?? null;
            $p = $profiles[(string) $r->user_id] ?? null;
            $t = $tickets[(string) $r->getKey()] ?? null;
            $pay = $payments[(string) $r->getKey()] ?? null;
            $cat = $cats[(string) $r->category_id] ?? null;
            $nat = strtolower((string) optional($p)->nationality);
            $name = optional($p)->fullName ?? optional($u)->name ?? '-';
            $qual = $quals[(string) $r->user_id . '|' . (string) $r->category_id] ?? null;
            $needQ = (bool) data_get($cat, 'qualification.required');

            return [
                'id' => (string) $r->getKey(),
                'code' => $r->registrationCode,
                'created' => optional($r->created_at)->format('d M Y'),
                'name' => $name,
                'initials' => strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $name) ?: '?', 0, 1) . (explode(' ', trim($name))[1][0] ?? '')),
                'email' => optional($u)->email,
                'phone' => optional($p)->phone ?? '-',
                'nat' => $nat,
                'country' => self::COUNTRY[$nat] ?? strtoupper($nat ?: '-'),
                'jersey' => $r->jerseySize,
                'bib' => optional($t)->bibNumber ?? '—',
                'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : '-',
                'category_id' => (string) $r->category_id,
                'payment' => $r->status,
                'amount' => $this->rp($r->totalAmount),
                'rate' => $nat === 'id' || $nat === '' ? 'Local' : 'Foreigner',
                'method' => $pay ? ($pay->gateway === 'manual' ? 'Manual (added by admin)' : ucwords(str_replace('_', ' ', (string) $pay->paymentMethod))) : '-',
                'paidAt' => $pay ? optional($pay->paidAt)->format('d M Y, H:i') : '-',
                'kit' => optional($t)->kitCollectedAt !== null,
                'kitAt' => optional(optional($t)->kitCollectedAt)->format('d M Y, H:i'),
                'kitBy' => optional($t)->kitCollectedBy,
                'em' => [
                    'name' => data_get($p, 'emergencyContact.name') ?? data_get($p, 'emergency_contact_name') ?? '',
                    'rel' => data_get($p, 'emergencyContact.relationship') ?? data_get($p, 'emergency_contact_relationship') ?? '',
                    'phone' => data_get($p, 'emergencyContact.phone') ?? data_get($p, 'emergency_contact_phone') ?? '',
                ],
                'qualification' => $qual ? 'Approved · ' . $this->km($qual->distanceKm) . ' km' : ($needQ ? 'Not approved' : 'Not required'),
                'receipt' => $pay ? url('/admin/participants/' . $r->getKey() . '/receipt') : null,
            ];
        });

        $stats = [
            'registered' => $all->count(),
            'paid' => $all->where('payment', 'paid')->count(),
            'pending' => $all->where('payment', 'pending')->count(),
            'kits' => $all->where('kit', true)->count(),
        ];

        $cat = $request->query('category');
        $pay = $request->query('payment');
        $kit = $request->query('kit');
        $s = mb_strtolower(trim((string) $request->query('q')));

        $filtered = $all->filter(function ($r) use ($cat, $pay, $kit, $s) {
            if ($cat && $r['category_id'] !== $cat) return false;
            if ($pay && $r['payment'] !== $pay) return false;
            if ($kit === 'collected' && ! $r['kit']) return false;
            if ($kit === 'not' && $r['kit']) return false;
            if ($s !== '' && ! str_contains(mb_strtolower($r['name'] . ' ' . $r['email'] . ' ' . $r['bib'] . ' ' . $r['code']), $s)) return false;
            return true;
        })->values();

        $perPage = 6;
        $pageNo = LengthAwarePaginator::resolveCurrentPage();
        $page = new LengthAwarePaginator($filtered->forPage($pageNo, $perPage)->values(), $filtered->count(), $perPage, $pageNo, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);
        $rows = $page->items();

        return view('admin.participants', compact('events', 'event', 'cats', 'stats', 'page', 'rows'));
    }

    public function update(Request $request, string $id)
    {
        $reg = Registration::findOrFail($id);
        $d = $request->validate([
            'full_name' => 'required|string|max:120',
            'phone' => 'required|string|max:25',
            'nationality' => 'required|in:id,my',
            'jersey_size' => 'required|in:XS,S,M,L,XL,XXL',
            'em_name' => 'nullable|string|max:120',
            'em_relationship' => 'nullable|string|max:60',
            'em_phone' => 'nullable|string|max:25',
        ]);

        $reg->update(['jerseySize' => $d['jersey_size']]);

        $p = Profile::firstOrNew(['user_id' => (string) $reg->user_id]);
        $p->forceFill([
            'fullName' => $d['full_name'], 'phone' => $d['phone'], 'nationality' => $d['nationality'],
            'emergencyContact' => ['name' => $d['em_name'], 'relationship' => $d['em_relationship'], 'phone' => $d['em_phone']],
        ])->save();

        User::where('_id', $reg->user_id)->update(['name' => $d['full_name']]);

        return back()->with('success', 'Participant updated.');
    }

    public function email(Request $request, string $id)
    {
        $d = $request->validate(['subject' => 'required|string|max:150', 'message' => 'required|string|max:3000']);
        $reg = Registration::findOrFail($id);
        $user = User::findOrFail($reg->user_id);

        try {
            Mail::raw($d['message'], fn ($m) => $m->to($user->email)->subject($d['subject']));
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['email' => 'The email could not be sent. Check the mail settings in .env.']);
        }

        return back()->with('success', 'Email sent to ' . $user->email . '.');
    }

    // Admin mendaftarkan peserta secara manual (langsung berstatus paid)
    public function store(Request $request, string $eventId, MidtransService $midtrans)
    {
        $event = RaceEvent::findOrFail($eventId);
        $d = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'phone' => 'required|string|max:25',
            'nationality' => 'required|in:id,my',
            'category_id' => 'required|string',
            'jersey_size' => 'required|in:XS,S,M,L,XL,XXL',
        ]);
        $category = RaceCategory::where('_id', $d['category_id'])->where('event_id', $eventId)->firstOrFail();
        $email = strtolower($d['email']);

        $user = User::where('email', $email)->first();
        if ($user && Registration::where('user_id', (string) $user->getKey())->where('event_id', $eventId)->whereIn('status', ['pending', 'paid'])->exists()) {
            return back()->withErrors(['participant' => 'This person is already registered for the event.']);
        }

        $took = RaceCategory::where('_id', $category->getKey())->where('slotsAvailable', '>', 0)->decrement('slotsAvailable');
        if (! $took) return back()->withErrors(['participant' => 'This category is sold out.']);

        $tempPassword = null;
        if (! $user) {
            $tempPassword = Str::random(10);
            $user = User::create(['name' => $d['name'], 'email' => $email, 'password' => Hash::make($tempPassword), 'role' => 'user']);
        }
        $uid = (string) $user->getKey();

        $profile = Profile::firstOrNew(['user_id' => $uid]);
        if (! $profile->exists) {
            $profile->forceFill([
                'fullName' => $d['name'], 'bibName' => strtoupper(explode(' ', trim($d['name']))[0]), 'nationality' => $d['nationality'],
                'phone' => self::PHONE[$d['nationality']] . ltrim(preg_replace('/\D/', '', $d['phone']), '0'),
            ])->save();
        }

        $price = (int) data_get($category, $d['nationality'] === 'id' ? 'price.local' : 'price.foreigner', 0);
        $reg = Registration::create([
            'user_id' => $uid, 'event_id' => $eventId, 'category_id' => (string) $category->getKey(),
            'registrationCode' => 'NTS-' . now()->format('y') . '-' . strtoupper(Str::random(6)),
            'jerseySize' => $d['jersey_size'], 'status' => 'paid', 'totalAmount' => $price, 'expiresAt' => now(),
        ]);
        Payment::create([
            'registration_id' => (string) $reg->getKey(), 'user_id' => $uid, 'orderId' => 'MAN-' . strtoupper(Str::random(8)),
            'purpose' => 'registration', 'gateway' => 'manual', 'paymentMethod' => 'manual', 'amount' => $price,
            'status' => 'settlement', 'paidAt' => now(),
        ]);
        $midtrans->issueTicket($reg);

        $msg = 'Participant added and marked as paid.';
        if ($tempPassword) $msg .= ' New account created · temporary password: ' . $tempPassword . ' (share it with the participant).';

        return redirect()->route('admin.participants.index', ['event' => $eventId])->with('success', $msg);
    }

    public function receipt(string $id)
    {
        $reg = Registration::findOrFail($id);
        $payment = Payment::where('registration_id', (string) $reg->getKey())->where('status', 'settlement')->firstOrFail();

        return view('admin.receipt', [
            'reg' => $reg, 'payment' => $payment,
            'user' => User::find($reg->user_id), 'event' => RaceEvent::find($reg->event_id),
            'cat' => RaceCategory::find($reg->category_id),
        ]);
    }
}