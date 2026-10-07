<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParticipantHelpers;
use App\Models\EventSubscription;
use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ParticipantHelpers;

    public function index(Request $request)
    {
        $uid = (string) $request->user()->getKey();
        $isLocal = $this->isLocal($uid);

        $events = RaceEvent::where('status', 'open')
            ->where('eventDate', '>=', now()->startOfDay())
            ->when($request->query('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('eventDate')
            ->paginate(6)
            ->withQueryString();

        $ids = $events->map(fn ($e) => (string) $e->getKey())->all();
        $cats = RaceCategory::whereIn('event_id', $ids)->orderBy('distanceKm')->get()->groupBy('event_id');
        $subs = EventSubscription::where('user_id', $uid)->get()->groupBy('event_id');

        $cards = $events->map(function ($e) use ($cats, $subs, $isLocal) {
            $id = (string) $e->getKey();
            $list = $cats[$id] ?? collect();
            $left = (int) $list->sum('slotsAvailable');

            return [
                'id' => $id,
                'name' => $e->name,
                'banner' => $e->bannerUrl,
                'date' => optional($e->eventDate)->format('d M Y'),
                'location' => collect([data_get($e, 'location.city'), data_get($e, 'location.province')])->filter()->implode(', '),
                'chips' => $list->map(fn ($c) => $this->km($c->distanceKm) . 'K')->all(),
                'from' => $list->map(fn ($c) => $this->priceFor($c, $isLocal))->filter()->min(),
                'left' => $left,
                'state' => $e->stateFor($left, (int) $list->sum('quota')),
                'subs' => collect($subs[$id] ?? [])->pluck('type')->all(),
            ];
        });

        return view('participant.events', compact('events', 'cards'));
    }

    public function show(Request $request, string $id)
    {
        $uid = (string) $request->user()->getKey();
        $event = RaceEvent::findOrFail($id);
        abort_if($event->status === 'draft', 404);

        $cats = RaceCategory::where('event_id', $id)->orderBy('distanceKm')->get();
        $state = $event->stateFor((int) $cats->sum('slotsAvailable'), (int) $cats->sum('quota'));
        $isLocal = $this->isLocal($uid);
        $mine = Registration::where('user_id', $uid)->where('event_id', $id)->whereIn('status', ['pending', 'paid'])->first();
        $approved = Qualification::where('user_id', $uid)->where('status', 'approved')->get()->pluck('category_id')->all();

        return view('participant.event-show', compact('event', 'cats', 'state', 'isLocal', 'mine', 'approved'));
    }

    // Join waitlist / Notify me
    public function subscribe(Request $request, string $id)
    {
        $type = $request->validate(['type' => 'required|in:waitlist,notify'])['type'];
        RaceEvent::findOrFail($id);

        EventSubscription::firstOrCreate([
            'user_id' => (string) $request->user()->getKey(),
            'event_id' => $id,
            'type' => $type,
        ]);

        return back()->with('success', $type === 'waitlist' ? 'You are on the waitlist for this event.' : 'Saved. We will notify you when registration opens.');
    }
}