@php
    $iconFor = fn ($b) => match (true) {
        str_contains($b, 'jersey') => 'ph-t-shirt',
        str_contains($b, 'medal') => 'ph-medal',
        str_contains($b, 'aid') => 'ph-fork-knife',
        str_contains($b, 'insurance') => 'ph-shield-plus',
        default => 'ph-check-circle',
    };
    $km = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<div x-show="tab === 'categories'" x-cloak class="mt-6 space-y-6">

@if($isNew)
    <div class="card p-10 text-center text-sm text-gray-500">Save the event first, then you can add race categories here.</div>
@else
    <div class="card">
        <div class="flex items-center justify-between p-6">
            <h2 class="text-lg font-semibold">Race categories</h2>
            <button type="button" @click="openCreate()" class="btn-primary"><i class="ph ph-plus"></i> Add category</button>
        </div>

        <table class="w-full">
            <thead class="bg-[#f8f7f2]">
                <tr>
                    <th class="th">Category</th>
                    <th class="th">Elevation / Cut-off</th>
                    <th class="th">Quota</th>
                    <th class="th">Local / Foreigner</th>
                    <th class="th">Qualification</th>
                    <th class="th">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#eceae2]">
                @forelse($categories as $c)
                    @php
                        $js = [
                            'id' => (string) $c->getKey(),
                            'name' => $c->name,
                            'distanceKm' => $c->distanceKm,
                            'elevationGain' => $c->elevationGain,
                            'cutOffHours' => $c->cutOffHours,
                            'quota' => $c->quota,
                            'price_local' => data_get($c, 'price.local'),
                            'price_foreigner' => data_get($c, 'price.foreigner'),
                            'benefits' => implode(', ', (array) ($c->benefits ?? [])),
                            'qualification_required' => (bool) data_get($c, 'qualification.required'),
                            'qualification_min_distance' => data_get($c, 'qualification.minDistanceKm'),
                            'qualification_years' => data_get($c, 'qualification.withinYears') ?? 2,
                            'registered' => $c->registered,
                        ];
                    @endphp
                    <tr>
                        <td class="td">
                            <p class="font-semibold">{{ $km($c->distanceKm) }}K · {{ $c->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $km($c->distanceKm) }} km</p>
                        </td>
                        <td class="td">{{ number_format((float) $c->elevationGain) }} m / {{ $km($c->cutOffHours) }} hours</td>
                        <td class="td">{{ $c->registered }} / {{ $c->quota }}</td>
                        <td class="td">{{ $rp(data_get($c, 'price.local')) }} / {{ $rp(data_get($c, 'price.foreigner')) }}</td>
                        <td class="td">
                            @if(data_get($c, 'qualification.required'))
                                <span class="chip bg-[#e3f0e8] text-forest-700">{{ $km(data_get($c, 'qualification.minDistanceKm')) }} km · last {{ data_get($c, 'qualification.withinYears') }} years</span>
                            @else
                                <span class="chip bg-[#f0efea] text-gray-500">Not required</span>
                            @endif
                        </td>
                        <td class="td">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="openEdit({{ \Illuminate\Support\Js::from($js) }})" class="font-semibold text-forest-700 hover:underline">Edit</button>
                                <div x-data="{ open: false }" class="relative">
                                    <button type="button" @click="open = !open" class="text-gray-400 hover:text-gray-700">
                                        <i class="ph ph-dots-three-bold text-lg"></i>
                                    </button>
                                    <div x-show="open" x-cloak @click.outside="open = false"
                                         class="absolute right-0 mt-1 w-44 bg-white border border-[#e9e7df] rounded-xl shadow-lg z-20 py-1">
                                        <form method="POST" action="{{ route('admin.categories.destroy', (string) $c->getKey()) }}"
                                              onsubmit="return confirm('Delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete category</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No categories yet. Click "Add category" to create the first one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-[#eceae2] text-sm text-gray-500">
            {{ $categories->count() }} {{ $categories->count() === 1 ? 'category' : 'categories' }} · {{ $categories->sum('quota') }} total slots · Prices in IDR
        </div>
    </div>

    <div class="rounded-2xl bg-[#e8f1ea] p-6 flex gap-4">
        <i class="ph ph-shield-check text-2xl text-forest-700"></i>
        <div>
            <p class="font-semibold">Set the right starting line for every runner</p>
            <p class="text-sm text-gray-600 mt-1">Qualification requirements apply before registration is approved. Existing registrations keep their original price when you update a category.</p>
        </div>
    </div>

    @if(count($common))
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Included in every registration</h3>
            <div class="flex flex-wrap gap-x-10 gap-y-3 text-sm">
                @foreach($common as $b)
                    <span class="inline-flex items-center gap-2.5">
                        <i class="ph {{ $iconFor($b) }} text-xl text-forest-700"></i>{{ ucfirst($b) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- MODAL ADD / EDIT CATEGORY -->
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="modal = false">
        <div class="absolute inset-0 bg-forest-900/50" @click="modal = false"></div>

        <div class="relative bg-white rounded-3xl w-full max-w-[720px] max-h-[92vh] overflow-y-auto shadow-2xl">
            <form method="POST"
                  :action="form.id ? '{{ url('/admin/categories') }}/' + form.id : '{{ route('admin.categories.store', $eventId) }}'">
                @csrf
                <input type="hidden" name="_method" :value="form.id ? 'PUT' : 'POST'">

                <div class="flex items-start justify-between p-8 pb-5 border-b border-[#eceae2]">
                    <div>
                        <h2 class="text-2xl font-semibold" x-text="form.id ? 'Edit category' : 'Add category'"></h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $event->name }} ·
                            <span x-text="form.id ? (parseFloat(form.distanceKm) + 'K ' + form.name) : 'New category'"></span>
                        </p>
                    </div>
                    <button type="button" @click="modal = false" class="text-gray-500 hover:text-gray-900"><i class="ph ph-x text-2xl"></i></button>
                </div>

                <div class="p-8 space-y-5">
                    <div>
                        <label class="lbl">Category name *</label>
                        <input class="inp" name="name" x-model="form.name" placeholder="Ridge Trail">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="lbl">Distance (km) *</label><input type="number" step="any" class="inp" name="distanceKm" x-model="form.distanceKm"></div>
                        <div><label class="lbl">Elevation gain (m) *</label><input type="number" step="any" class="inp" name="elevationGain" x-model="form.elevationGain"></div>
                        <div><label class="lbl">Cut-off (hours) *</label><input type="number" step="any" class="inp" name="cutOffHours" x-model="form.cutOffHours"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="lbl">Quota *</label><input type="number" class="inp" name="quota" x-model="form.quota"></div>
                        <div><label class="lbl">Local price (IDR) *</label><input type="number" step="any" class="inp" name="price_local" x-model="form.price_local"></div>
                        <div><label class="lbl">Foreigner price (IDR) *</label><input type="number" step="any" class="inp" name="price_foreigner" x-model="form.price_foreigner"></div>
                    </div>
                    <div>
                        <label class="lbl">Benefits</label>
                        <textarea name="benefits" rows="3" x-model="form.benefits" class="inp h-auto py-3"
                                  placeholder="Race jersey, finisher medal, aid stations, race insurance"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Separate each benefit with a comma.</p>
                    </div>

                    <!-- Kualifikasi -->
                    <div class="rounded-2xl border border-[#cfe3d6] bg-[#eaf3ed] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold">Require qualification</p>
                                <p class="text-sm text-gray-500">Runners must submit proof of a previous finish.</p>
                            </div>
                            <button type="button" role="switch" :aria-checked="form.qualification_required"
                                    @click="form.qualification_required = !form.qualification_required"
                                    :class="form.qualification_required ? 'bg-forest-700' : 'bg-gray-300'"
                                    class="relative w-12 h-7 rounded-full transition-colors shrink-0">
                                <span class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform"
                                      :class="form.qualification_required ? 'translate-x-5' : ''"></span>
                            </button>
                        </div>
                        <input type="hidden" name="qualification_required" :value="form.qualification_required ? 1 : 0">

                        <div x-show="form.qualification_required" x-cloak>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div><label class="lbl">Minimum distance (km) *</label><input type="number" step="any" class="inp" name="qualification_min_distance" x-model="form.qualification_min_distance"></div>
                                <div><label class="lbl">Within the last (years) *</label><input type="number" class="inp" name="qualification_years" x-model="form.qualification_years"></div>
                            </div>
                            <p class="text-sm text-forest-700 mt-3">
                                A completed race of at least <span x-text="form.qualification_min_distance || '…'"></span> km
                                within <span x-text="form.qualification_years || '…'"></span> years of {{ optional($event->eventDate)->format('d M Y') }} is required.
                            </p>
                        </div>
                    </div>

                    <p x-show="form.id" x-cloak class="text-sm text-gray-500">
                        <span x-text="form.registered"></span> runners are registered. The quota cannot be lower than the current registration count.
                    </p>
                </div>

                <div class="flex items-center justify-between px-8 py-5 bg-[#f8f7f2] rounded-b-3xl border-t border-[#eceae2]">
                    <span class="text-xs text-gray-500">* Required fields</span>
                    <div class="flex gap-3">
                        <button type="button" @click="modal = false" class="btn-outline">Cancel</button>
                        <button type="submit" class="btn-primary">Save category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

</div>