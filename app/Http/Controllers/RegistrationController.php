<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Result;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    use ParticipantHelpers;

    const HOLD_MINUTES = 15;

    public function __construct(private MidtransService $midtrans) {}

    // My events
    public function index(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        $rows = Registration::where('user_id', $uid)->whereIn('status', ['pending', 'paid'])->get()
            ->map(function ($r) {
                $event = RaceEvent::find($r->event_id);
                $cat = RaceCategory::find($r->category_id);
                $result = Result::where('registration_id', (string) $r->getKey())->first();
                $completed = $r->status === 'paid' && optional($event)->status === 'finished';

                return [
                    'id' => (string) $r->getKey(),
                    'event' => optional($event)->name ?? '-',
                    'event_id' => (string) $r->event_id,
                    'category' => $cat ? $this->km($cat->distanceKm) . 'K' : '-',
                    'label' => $r->status === 'pending' ? 'PENDING PAYMENT' : ($completed ? 'COMPLETED' : 'PAID'),
                    'pending' => $r->status === 'pending' && now()->lt($r->expiresAt),
                    'time' => optional($result)->finishTime ?? '00:00:00',
                    'date' => optional(optional($event)->eventDate)->timestamp ?? 0,
                ];
            })
            ->sortByDesc('date')->values();

        return view('participant.my-events', compact('rows'));
    }

    // WAR SLOT: rebut slot lalu buat reservasi
    public function store(Request $request, string $eventId)
    {
        $data = $request->validate([
            'category_id' => 'required|string',
            'jersey_size' => 'required|in:XS,S,M,L,XL,XXL',
        ]);
        $uid = (string) $request->user()->getKey();

        $event = RaceEvent::findOrFail($eventId);
        $category = RaceCategory::where('_id', $data['category_id'])->where('event_id', $eventId)->firstOrFail();

        $open = $event->status === 'open'
            && $event->registrationOpenAt && now()->gte($event->registrationOpenAt)
            && $event->registrationCloseAt && now()->lte($event->registrationCloseAt);
        if (! $open) return back()->withErrors(['registration' => 'Registration is not open for this event.']);

        $profile = Profile::where('user_id', $uid)->first();
        if (! $profile || blank($profile->fullName) || blank($profile->phone) || blank($profile->dateOfBirth) || blank($profile->nationality)) {
            return redirect('/profil')->withErrors(['profile' => 'Please complete your profile before registering.']);
        }

        if (Registration::where('user_id', $uid)->where('event_id', $eventId)->whereIn('status', ['pending', 'paid'])->exists()) {
            return back()->withErrors(['registration' => 'You are already registered for this event.']);
        }

        if (data_get($category, 'qualification.required')) {
            $ok = Qualification::where('user_id', $uid)->where('category_id', (string) $category->getKey())->where('status', 'approved')->exists();
            if (! $ok) return back()->withErrors(['registration' => 'This category requires an approved qualification.']);
        }

        // Pengurangan kuota atomik: hanya berhasil jika slotsAvailable masih > 0
        $took = RaceCategory::where('_id', $category->getKey())->where('slotsAvailable', '>', 0)->decrement('slotsAvailable');
        if (! $took) return back()->withErrors(['registration' => 'Sorry, this category is sold out.']);

        try {
            $reg = Registration::create([
                'user_id' => $uid,
                'event_id' => $eventId,
                'category_id' => (string) $category->getKey(),
                'registrationCode' => 'NTS-' . now()->format('y') . '-' . strtoupper(Str::random(6)),
                'jerseySize' => $data['jersey_size'],
                'status' => 'pending',
                'totalAmount' => $this->priceFor($category, $this->isLocal($uid)),
                'expiresAt' => now()->addMinutes(self::HOLD_MINUTES),
            ]);
        } catch (\Throwable $e) {
            RaceCategory::where('_id', $category->getKey())->increment('slotsAvailable');
            throw $e;
        }

        return redirect('/registrations/' . $reg->getKey() . '/pay');
    }

    public function pay(Request $request, string $id)
    {
        $reg = Registration::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->firstOrFail();
        if ($reg->status === 'paid') return redirect('/event-saya')->with('success', 'Payment received. Your BIB is ready.');

        $event = RaceEvent::find($reg->event_id);
        $cat = RaceCategory::find($reg->category_id);

        return view('participant.pay', [
            'heading' => 'Complete your payment',
            'lines' => [
                ['Event', $event->name],
                ['Category', $this->km($cat->distanceKm) . 'K ' . $cat->name],
                ['Registration code', $reg->registrationCode],
                ['Jersey size', $reg->jerseySize],
            ],
            'total' => (int) $reg->totalAmount,
            'expiresAt' => $reg->expiresAt,
            'active' => $reg->status === 'pending' && now()->lt($reg->expiresAt),
            'checkoutUrl' => url("/registrations/{$id}/checkout"),
            'cancelUrl' => url("/registrations/{$id}"),
        ]);
    }

    public function cancel(Request $request, string $id)
    {
        $reg = Registration::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->firstOrFail();

        $this->midtrans->releaseRegistration($reg, 'cancelled');
        Payment::where('registration_id', (string) $reg->getKey())->where('status', 'pending')->update(['status' => 'cancel']);

        return redirect('/events')->with('success', 'Reservation cancelled and the slot was released.');
    }
}