<?php

namespace App\Http\Controllers;

use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:60',
            'distanceKm' => 'required|numeric|min:1',
            'elevationGain' => 'required|numeric|min:0',
            'cutOffHours' => 'required|numeric|min:1',
            'quota' => 'required|integer|min:1',
            'price_local' => 'required|numeric|min:0',
            'price_foreigner' => 'required|numeric|min:0',
            'benefits' => 'nullable|string|max:1000',
            'qualification_required' => 'nullable|boolean',
            'qualification_min_distance' => 'required_if:qualification_required,1|nullable|numeric|min:1',
            'qualification_years' => 'required_if:qualification_required,1|nullable|integer|min:1|max:10',
        ];
    }

    private function payload(Request $request, array $d): array
    {
        $required = $request->boolean('qualification_required');

        return [
            'name' => $d['name'],
            'distanceKm' => (float) $d['distanceKm'],
            'elevationGain' => (float) $d['elevationGain'],
            'cutOffHours' => (float) $d['cutOffHours'],
            'quota' => (int) $d['quota'],
            'price' => [
                'local' => (float) $d['price_local'],
                'foreigner' => (float) $d['price_foreigner'],
            ],
            'benefits' => collect(explode(',', $d['benefits'] ?? ''))
                ->map(fn ($b) => trim($b))->filter()->values()->all(),
            'qualification' => [
                'required' => $required,
                'minDistanceKm' => $required ? (float) $d['qualification_min_distance'] : null,
                'withinYears' => $required ? (int) $d['qualification_years'] : null,
            ],
        ];
    }

    private function done(string $eventId, string $message)
    {
        return redirect()
            ->route('admin.events.edit', ['id' => $eventId, 'tab' => 'categories'])
            ->with('success', $message);
    }

    public function store(Request $request, string $eventId)
    {
        RaceEvent::findOrFail($eventId);

        $data = $this->payload($request, $request->validate($this->rules()));
        $data['event_id'] = $eventId;
        $data['slotsAvailable'] = $data['quota'];

        RaceCategory::create($data);

        return $this->done($eventId, 'Category added.');
    }

    public function update(Request $request, string $id)
    {
        $category = RaceCategory::findOrFail($id);
        $data = $this->payload($request, $request->validate($this->rules()));

        $taken = $category->quota - $category->slotsAvailable;
        if ($data['quota'] < $taken) {
            return redirect()
                ->route('admin.events.edit', ['id' => $category->event_id, 'tab' => 'categories'])
                ->withErrors(['quota' => "Quota cannot be lower than the {$taken} runners already registered."]);
        }

        $data['slotsAvailable'] = $data['quota'] - $taken;
        $category->update($data);

        return $this->done($category->event_id, 'Category updated.');
    }

    public function destroy(string $id)
    {
        $category = RaceCategory::findOrFail($id);
        $eventId = $category->event_id;

        if (Registration::where('category_id', $id)->exists()) {
            return redirect()
                ->route('admin.events.edit', ['id' => $eventId, 'tab' => 'categories'])
                ->withErrors(['category' => 'This category already has registrations and cannot be deleted.']);
        }

        $category->delete();

        return $this->done($eventId, 'Category deleted.');
    }
}