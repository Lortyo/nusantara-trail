<?php

namespace App\Http\Controllers;

use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEventController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'eventDate' => 'required|date',
            'startTime' => 'required|date_format:H:i',
            'location.venue' => 'required|string|max:200',
            'location.city' => 'required|string|max:100',
            'location.province' => 'required|string|max:100',
            'description' => 'nullable|string|max:3000',
            'organizer' => 'nullable|string|max:150',
            'contactEmail' => 'nullable|email',
            'timezone' => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'currency' => 'required|in:IDR',
            'registrationOpenAt' => 'nullable|required_with:registrationCloseAt|date',
            'registrationCloseAt' => 'nullable|required_with:registrationOpenAt|date|after:registrationOpenAt|before_or_equal:eventDate',
            'transferDeadline' => 'nullable|date',
            'categoryChangeDeadline' => 'nullable|date',
            'bannerUrl' => 'nullable|url',
        ];
    }

    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    private function makeSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        while (RaceEvent::where('slug', $slug)->exists()) {
            $slug = $base . '-' . Str::lower(Str::random(4));
        }
        return $slug;
    }

    private function editData(RaceEvent $event): array
    {
        $categories = $event->exists
            ? RaceCategory::where('event_id', (string) $event->getKey())->orderBy('distanceKm')->get()
            : collect();

        $categories->each(fn ($c) => $c->registered = $c->quota - $c->slotsAvailable);

        $common = [];
        if ($categories->isNotEmpty()) {
            $lists = $categories
                ->map(fn ($c) => array_map(fn ($b) => mb_strtolower(trim($b)), (array) ($c->benefits ?? [])))
                ->all();
            $common = array_values(array_intersect(...$lists));
        }

        $ready = [
            'info' => filled($event->name) && filled($event->eventDate) && filled(data_get($event, 'location.venue')),
            'categories' => $categories->count(),
            'dates' => filled($event->registrationOpenAt) && filled($event->registrationCloseAt),
        ];

        return compact('event', 'categories', 'common', 'ready');
    }

    // Daftar event
    public function index(Request $request)
    {
        $all = RaceEvent::all();
        $counts = [
            'total' => $all->count(),
            'open' => $all->where('status', 'open')->count(),
            'draft' => $all->where('status', 'draft')->count(),
        ];

        $events = RaceEvent::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('season'), function ($q, $year) {
                $year = (int) $year;
                return $q->whereBetween('eventDate', [
                    Carbon::create($year, 1, 1)->startOfDay(),
                    Carbon::create($year, 12, 31)->endOfDay(),
                ]);
            })
            ->orderBy('eventDate', 'desc')
            ->paginate(10)
            ->withQueryString();

        $ids = $events->map(fn ($e) => (string) $e->getKey())->all();
        $cats = RaceCategory::whereIn('event_id', $ids)->get()->groupBy('event_id');

        $stats = [];
        foreach ($events as $e) {
            $id = (string) $e->getKey();
            $list = ($cats[$id] ?? collect())->sortBy('distanceKm');
            $stats[$id] = [
                'label' => $list->map(fn ($c) => $this->km($c->distanceKm) . 'K')->implode(' / ') ?: '-',
                'taken' => $list->sum(fn ($c) => $c->quota - $c->slotsAvailable),
                'total' => $list->sum('quota'),
            ];
        }

        return view('admin.events.index', compact('events', 'counts', 'stats'));
    }

    public function create()
    {
        $event = new RaceEvent(['status' => 'draft', 'timezone' => 'Asia/Makassar', 'currency' => 'IDR']);
        return view('admin.events.edit', $this->editData($event));
    }

    public function edit(string $id)
    {
        $event = RaceEvent::findOrFail($id);
        return view('admin.events.edit', $this->editData($event));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['slug'] = $this->makeSlug($data['name']);
        $data['status'] = 'draft';
        $data['bibSequence'] = 0;
        $data['created_by'] = (string) $request->user()->getKey();

        $event = RaceEvent::create($data);

        return redirect()
            ->route('admin.events.edit', ['id' => (string) $event->getKey(), 'tab' => 'categories'])
            ->with('success', 'Event created. Now add the race categories.');
    }

    public function update(Request $request, string $id)
    {
        $event = RaceEvent::findOrFail($id);
        $event->update($request->validate($this->rules()));

        return redirect()
            ->route('admin.events.edit', ['id' => $id, 'tab' => $request->input('tab', 'info')])
            ->with('success', 'Changes saved.');
    }

    // publish / close / reopen / finish
    public function changeStatus(Request $request, string $id)
    {
        $event = RaceEvent::findOrFail($id);
        $action = $request->validate(['action' => 'required|in:publish,close,reopen,finish'])['action'];

        if (in_array($action, ['publish', 'reopen'])) {
            $hasCategories = RaceCategory::where('event_id', $id)->exists();
            if (! $hasCategories || blank($event->registrationOpenAt) || blank($event->registrationCloseAt)) {
                return back()->withErrors(['status' => 'Add at least one category and set the registration dates before opening registration.']);
            }
            $event->update(['status' => 'open']);
        } elseif ($action === 'close') {
            $event->update(['status' => 'closed']);
        } else {
            $event->update(['status' => 'finished']);
        }

        return back()->with('success', 'Event status updated.');
    }

    public function destroy(string $id)
    {
        $event = RaceEvent::findOrFail($id);

        if (Registration::where('event_id', $id)->exists()) {
            return back()->withErrors(['event' => 'This event already has registrations and cannot be deleted.']);
        }

        RaceCategory::where('event_id', $id)->delete();
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }
}