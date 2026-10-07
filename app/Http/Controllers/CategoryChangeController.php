<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Models\CategoryChange;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use Illuminate\Http\Request;

class CategoryChangeController extends Controller
{
    use ParticipantHelpers;

    const ADMIN_FEE = 25000;
    const HOLD_MINUTES = 15;

    private function requirements(string $uid, RaceCategory $to, bool $isUpgrade): array
    {
        $list = [];

        if (data_get($to, 'qualification.required')) {
            $ok = Qualification::where('user_id', $uid)->where('category_id', (string) $to->getKey())->where('status', 'approved')->exists();
            $list[] = [
                'title' => 'Trail finish ≥ ' . $this->km(data_get($to, 'qualification.minDistanceKm')) . 'K / ' . data_get($to, 'qualification.withinYears') . ' years',
                'ok' => $ok,
                'note' => $ok ? 'Eligible' : 'Qualification not approved yet',
            ];
        }
        if ($isUpgrade) {
            $list[] = ['title' => 'Medical certificate', 'ok' => null, 'note' => 'PDF, JPG or PNG, max 5 MB'];
        }

        return $list;
    }

    private function deadline(RaceEvent $event)
    {
        return $event->categoryChangeDeadline ?? $event->registrationCloseAt;
    }

    public function show(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        $regs = Registration::where('user_id', $uid)->where('status', 'paid')->get()
            ->filter(fn ($r) => optional(RaceEvent::find($r->event_id))->status !== 'finished')->values();
        $wanted = $request->query('registration');
        $reg = $wanted ? $regs->first(fn ($r) => (string) $r->getKey() === $wanted) : $regs->first();

        if (! $reg) return view('participant.change-category', ['reg' => null]);

        $event = RaceEvent::find($reg->event_id);
        $current = RaceCategory::find($reg->category_id);
        $cats = RaceCategory::where('event_id', (string) $event->getKey())->orderBy('distanceKm')->get();
        $isLocal = $this->isLocal($uid);
        $deadline = $this->deadline($event);
        $open = $deadline && now()->lt($deadline);
        $pending = CategoryChange::where('registration_id', (string) $reg->getKey())
            ->where('status', 'pending_payment')->where('expiresAt', '>', now())->first();

        $selected = $cats->first(fn ($c) => (string) $c->getKey() === $request->query('to') && (string) $c->getKey() !== (string) $current->getKey());
        $preview = null;
        if ($selected) {
            $newPrice = $this->priceFor($selected, $isLocal);
            $diff = $newPrice - (int) $reg->totalAmount;
            $isUpgrade = $selected->distanceKm > $current->distanceKm;
            $preview = [
                'newPrice' => $newPrice, 'diff' => $diff, 'fee' => self::ADMIN_FEE,
                'total' => max($diff, 0) + self::ADMIN_FEE, 'isUpgrade' => $isUpgrade,
                'requirements' => $this->requirements($uid, $selected, $isUpgrade),
            ];
        }

        return view('participant.change-category', compact('reg', 'event', 'current', 'cats', 'isLocal', 'deadline', 'open', 'pending', 'selected', 'preview'));
    }

    public function store(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        $reg = Registration::where('_id', $request->input('registration_id'))->where('user_id', $uid)->where('status', 'paid')->firstOrFail();
        $event = RaceEvent::findOrFail($reg->event_id);
        $current = RaceCategory::findOrFail($reg->category_id);
        $to = RaceCategory::where('_id', $request->input('to_category_id'))->where('event_id', (string) $event->getKey())->firstOrFail();
        $back = '/change-category?registration=' . $reg->getKey() . '&to=' . $to->getKey();

        $deadline = $this->deadline($event);
        if (! $deadline || now()->gte($deadline)) return redirect($back)->withErrors(['change' => 'Category changes are closed for this event.']);
        if ((string) $to->getKey() === (string) $current->getKey()) return redirect($back)->withErrors(['change' => 'Choose a different category.']);

        if (CategoryChange::where('registration_id', (string) $reg->getKey())->where('status', 'pending_payment')->where('expiresAt', '>', now())->exists()) {
            return redirect($back)->withErrors(['change' => 'You already have a category change waiting for payment.']);
        }

        $isUpgrade = $to->distanceKm > $current->distanceKm;
        $request->validate(['medical_certificate' => [$isUpgrade ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);

        foreach ($this->requirements($uid, $to, $isUpgrade) as $req) {
            if ($req['ok'] === false) return redirect($back)->withErrors(['change' => $req['title'] . ': ' . $req['note']]);
        }

        // Tahan slot kategori tujuan secara atomik
        $took = RaceCategory::where('_id', $to->getKey())->where('slotsAvailable', '>', 0)->decrement('slotsAvailable');
        if (! $took) return redirect($back)->withErrors(['change' => 'The selected category is sold out.']);

        $newPrice = $this->priceFor($to, $this->isLocal($uid));
        $diff = $newPrice - (int) $reg->totalAmount;
        $path = $request->file('medical_certificate')
            ? $request->file('medical_certificate')->store('medical-certificates', 'public') : null;

        $change = CategoryChange::create([
            'registration_id' => (string) $reg->getKey(),
            'user_id' => $uid,
            'event_id' => (string) $event->getKey(),
            'from_category_id' => (string) $current->getKey(),
            'to_category_id' => (string) $to->getKey(),
            'newPrice' => $newPrice,
            'priceDifference' => $diff,
            'adminFee' => self::ADMIN_FEE,
            'total' => max($diff, 0) + self::ADMIN_FEE,
            'certificatePath' => $path,
            'status' => 'pending_payment',
            'expiresAt' => now()->addMinutes(self::HOLD_MINUTES),
        ]);

        return redirect('/category-changes/' . $change->getKey() . '/pay');
    }

    public function pay(Request $request, string $id)
    {
        $chg = CategoryChange::where('_id', $id)->where('user_id', (string) $request->user()->getKey())->firstOrFail();
        if ($chg->status === 'completed') return redirect('/event-saya')->with('success', 'Your category has been changed.');

        $from = RaceCategory::find($chg->from_category_id);
        $to = RaceCategory::find($chg->to_category_id);
        $rp = fn ($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');

        return view('participant.pay', [
            'heading' => 'Pay for category change',
            'lines' => [
                ['Event', RaceEvent::find($chg->event_id)->name],
                ['From', $this->km($from->distanceKm) . 'K ' . $from->name],
                ['To', $this->km($to->distanceKm) . 'K ' . $to->name],
                ['Price difference', $rp($chg->priceDifference)],
                ['Administration fee', $rp($chg->adminFee)],
            ],
            'total' => (int) $chg->total,
            'expiresAt' => $chg->expiresAt,
            'active' => $chg->status === 'pending_payment' && now()->lt($chg->expiresAt),
            'checkoutUrl' => url("/category-changes/{$id}/checkout"),
            'cancelUrl' => null,
        ]);
    }
}