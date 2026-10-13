<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    public function show(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        // kategori yang butuh kualifikasi pada event yang masih open
        $options = RaceCategory::where('qualification.required', true)->get()
            ->map(fn ($c) => ['cat' => $c, 'event' => RaceEvent::find($c->event_id)])
            ->filter(fn ($x) => $x['event'] && $x['event']->status === 'open')->values();

        // The category link from an event is authoritative. MongoDB's nested
        // field query or an event status change must not turn that link into an
        // empty qualification page.
        $requestedCategoryId = $request->query('category');
        if ($requestedCategoryId && ! $options->contains(fn ($x) => (string) $x['cat']->getKey() === (string) $requestedCategoryId)) {
            $category = RaceCategory::find($requestedCategoryId);
            $event = $category ? RaceEvent::find($category->event_id) : null;

            if ($category && $event && data_get($category, 'qualification.required')) {
                $options->prepend(['cat' => $category, 'event' => $event]);
            }
        }

        $selected = $options->first(fn ($x) => (string) $x['cat']->getKey() === (string) $requestedCategoryId) ?? $options->first();

        $mine = Qualification::where('user_id', $uid)->orderBy('created_at', 'desc')->get()->map(function ($q) {
            $cat = RaceCategory::find($q->category_id);
            $ev = RaceEvent::find($q->event_id);
            return [
                'code' => $q->requestCode, 'status' => $q->status, 'note' => $q->reviewNote,
                'label' => optional($ev)->name . ' · ' . ($cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : ''),
                'race' => $q->raceName, 'date' => optional($q->raceDate)->format('d M Y'),
            ];
        });

        $existing = $selected
            ? Qualification::where('user_id', $uid)->where('category_id', (string) $selected['cat']->getKey())
                ->whereIn('status', ['pending', 'approved'])->first()
            : null;

        $req = null;
        if ($selected) {
            $years = (int) data_get($selected['cat'], 'qualification.withinYears');
            $req = [
                'min' => $this->km(data_get($selected['cat'], 'qualification.minDistanceKm')),
                'years' => $years,
                'earliest' => ($selected['event']->eventDate ?? now())->copy()->subYears($years),
            ];
        }

        return view('participant.qualification', compact('options', 'selected', 'mine', 'existing', 'req'));
    }

    public function store(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        $category = RaceCategory::findOrFail($request->input('category_id'));
        abort_unless(data_get($category, 'qualification.required'), 404);
        $event = RaceEvent::findOrFail($category->event_id);

        $min = (float) data_get($category, 'qualification.minDistanceKm');
        $years = (int) data_get($category, 'qualification.withinYears');
        $earliest = ($event->eventDate ?? now())->copy()->subYears($years)->startOfDay();
        $back = '/qualification?category=' . $category->getKey();

        if (Qualification::where('user_id', $uid)->where('category_id', (string) $category->getKey())->whereIn('status', ['pending', 'approved'])->exists()) {
            return redirect($back)->withErrors(['qualification' => 'You already have a pending or approved qualification for this category.']);
        }

        $data = $request->validate([
            'race_name' => 'required|string|max:150',
            'race_date' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $earliest->toDateString()],
            'distance_km' => 'required|numeric|min:' . $min,
            'finish_time' => ['required', 'regex:/^\d{1,3}:[0-5]\d:[0-5]\d$/'],
            'race_bib' => 'nullable|string|max:20',
            'result_url' => 'nullable|url',
            'proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'distance_km.min' => "The race must be at least {$min} km.",
            'race_date.after_or_equal' => 'The race must be on or after ' . $earliest->format('d M Y') . '.',
            'finish_time.regex' => 'Use the format HH:MM:SS, for example 14:32:18.',
        ]);

        $file = $request->file('proof');
        $q = Qualification::create([
            'user_id' => $uid,
            'event_id' => (string) $event->getKey(),
            'category_id' => (string) $category->getKey(),
            'raceName' => $data['race_name'],
            'raceDate' => $data['race_date'],
            'distanceKm' => (float) $data['distance_km'],
            'finishTime' => $data['finish_time'],
            'raceBib' => $data['race_bib'] ?? null,
            'resultUrl' => $data['result_url'] ?? null,
            'proofPath' => $file->store('qualifications', 'public'),
            'proofName' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);
        $q->update(['requestCode' => 'Q-' . strtoupper(substr((string) $q->getKey(), -6))]);

        return redirect($back)->with('success', 'Submitted. The committee will review your proof and you will be notified by email.');
    }
}
