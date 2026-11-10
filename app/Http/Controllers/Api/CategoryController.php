<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    private function rules(bool $partial): array
    {
        $r = $partial ? 'sometimes|required' : 'required';

        return [
            'name' => "$r|string|max:60",
            'distanceKm' => "$r|numeric|min:1",
            'elevationGain' => "$r|numeric|min:0",
            'cutOffHours' => "$r|numeric|min:1",
            'quota' => "$r|integer|min:1",
            'price' => "$r|array",
            'price.local' => "$r|numeric|min:0",
            'price.foreigner' => "$r|numeric|min:0",
            'benefits' => 'nullable|array',
            'benefits.*' => 'string|max:100',
            'qualification' => 'nullable|array',
            'qualification.required' => 'nullable|boolean',
            'qualification.minDistanceKm' => 'nullable|numeric|min:1',
            'qualification.withinYears' => 'nullable|integer|min:1|max:10',
        ];
    }

    private function invalid($validator)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 400);
    }

    private function normalize(array $data): array
    {
        if (isset($data['price'])) {
            $data['price'] = array_map('floatval', $data['price']);
        }
        if (array_key_exists('qualification', $data)) {
            $q = $data['qualification'] ?? [];
            $required = (bool) ($q['required'] ?? false);
            $data['qualification'] = [
                'required' => $required,
                'minDistanceKm' => $required ? (float) ($q['minDistanceKm'] ?? 0) : null,
                'withinYears' => $required ? (int) ($q['withinYears'] ?? 0) : null,
            ];
        }

        return $data;
    }

    public function index(string $eventId)
    {
        if (! RaceEvent::where('_id', $eventId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $categories = RaceCategory::where('event_id', $eventId)->orderBy('distanceKm')->get();

        return response()->json(['success' => true, 'message' => 'OK', 'data' => $categories]);
    }

    public function store(Request $request, string $eventId)
    {
        RaceEvent::findOrFail($eventId);

        $validator = Validator::make($request->all(), $this->rules(false));
        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $data = $this->normalize($validator->validated());
        $data['qualification'] ??= ['required' => false, 'minDistanceKm' => null, 'withinYears' => null];
        $data['benefits'] = array_values($data['benefits'] ?? []);
        $data['event_id'] = $eventId;
        $data['slotsAvailable'] = $data['quota'];

        $category = RaceCategory::create($data);

        return response()->json(['success' => true, 'message' => 'Kategori dibuat', 'data' => $category], 201);
    }

    public function update(Request $request, string $id)
    {
        $category = RaceCategory::findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules(true));
        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $data = $this->normalize($validator->validated());

        if (isset($data['price'])) {
            $data['price'] = array_merge((array) $category->price, $data['price']);
        }

        if (isset($data['quota'])) {
            $taken = $category->quota - $category->slotsAvailable;
            if ($data['quota'] < $taken) {
                return response()->json([
                    'success' => false,
                    'message' => "Kuota tidak boleh kurang dari {$taken} peserta yang sudah mendaftar",
                ], 409);
            }
            $data['slotsAvailable'] = $data['quota'] - $taken;
        }

        $category->update($data);

        return response()->json(['success' => true, 'message' => 'Kategori diperbarui', 'data' => $category->fresh()]);
    }

    public function destroy(string $id)
    {
        $category = RaceCategory::findOrFail($id);

        if (Registration::where('category_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sudah memiliki pendaftaran dan tidak dapat dihapus',
            ], 409);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Kategori dihapus']);
    }
}