@extends('layouts.admin')

@section('title', $event->exists ? $event->name : 'New event')
@section('breadcrumb', 'Events')

@section('content')
    @php
        $isNew = !$event->exists;
        $eventId = $isNew ? null : (string) $event->getKey();
        $tabs = ['info' => 'Info', 'categories' => 'Categories', 'settings' => 'Settings'];
        $dt = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
        $statusText = [
            'draft' => 'This event is a draft. Only your team can see it.',
            'open' => 'Your event is live. Changes to race information will appear on the public registration page.',
            'closed' => 'Registration is closed. Runners can no longer register.',
            'finished' => 'This event is completed.',
        ];
        $actions = [
            'draft' => [['publish', 'Publish event', 'btn-primary']],
            'open' => [['close', 'Close registration', 'btn-outline']],
            'closed' => [['reopen', 'Reopen registration', 'btn-outline'], ['finish', 'Mark as completed', 'btn-outline']],
            'finished' => [],
        ];
    @endphp

    <div x-data="eventPage('{{ request('tab', 'info') }}', '{{ optional($event->eventDate)->format('d M Y') }}')">

        <!-- Judul + tombol -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-[28px] font-semibold tracking-tight text-forest-900">
                    {{ $isNew ? 'New event' : $event->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Event settings / {{ $isNew ? 'not saved yet' : strtoupper($event->slug) }}
                    @unless($isNew) · Last saved {{ optional($event->updated_at)->format('d M Y, H:i') }} @endunless
                </p>
            </div>
            <div class="flex gap-3">
                @unless($isNew)
                    <a href="{{ url('/events') }}" target="_blank" class="btn-outline"><i class="ph ph-arrow-square-out"></i>
                        Preview event</a>
                @endunless
                <button type="submit" form="event-form"
                    class="btn-primary">{{ $isNew ? 'Create event' : 'Save changes' }}</button>
            </div>
        </div>

        <!-- Tab -->
        <div class="flex gap-8 border-b border-[#e2e0d8] mt-6">
            @foreach($tabs as $key => $label)
                <button type="button" @click="setTab('{{ $key }}')"
                    :class="tab === '{{ $key }}' ? 'text-forest-700 border-forest-700' : 'text-gray-500 border-transparent hover:text-gray-900'"
                    class="pb-3 -mb-px text-sm font-semibold border-b-2 transition-colors">{{ $label }}</button>
            @endforeach
        </div>

        <!-- TAB INFO + SETTINGS -->
        <div x-show="tab !== 'categories'" x-cloak class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6 mt-6 items-start">

            <form id="event-form" method="POST"
                action="{{ $isNew ? route('admin.events.store') : route('admin.events.update', $eventId) }}">
                @csrf
                @unless($isNew) @method('PUT') @endunless
                <input type="hidden" name="tab" :value="tab">

                <!-- Info -->
                <div x-show="tab === 'info'" x-cloak class="card p-6">
                    <h2 class="text-lg font-semibold mb-5">Event information</h2>
                    <div class="space-y-5">
                        <div>
                            <label class="lbl">Event name *</label>
                            <input class="inp" name="name" value="{{ old('name', $event->name) }}">
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="lbl">Race date *</label>
                                <input type="date" class="inp" name="eventDate"
                                    value="{{ old('eventDate', optional($event->eventDate)->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label class="lbl">Start time *</label>
                                <input type="time" class="inp" name="startTime"
                                    value="{{ old('startTime', $event->startTime) }}">
                            </div>
                        </div>
                        <div>
                            <label class="lbl">Venue *</label>
                            <input class="inp" name="location[venue]"
                                value="{{ old('location.venue', data_get($event, 'location.venue')) }}">
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="lbl">City *</label>
                                <input class="inp" name="location[city]"
                                    value="{{ old('location.city', data_get($event, 'location.city')) }}">
                            </div>
                            <div>
                                <label class="lbl">Province *</label>
                                <input class="inp" name="location[province]"
                                    value="{{ old('location.province', data_get($event, 'location.province')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="lbl">Event description</label>
                            <textarea name="description" rows="4"
                                class="inp h-auto py-3">{{ old('description', $event->description) }}</textarea>
                        </div>
                        <div>
                            <label class="lbl">Banner image URL</label>
                            <input class="inp" name="bannerUrl" value="{{ old('bannerUrl', $event->bannerUrl) }}"
                                placeholder="https://...">
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="lbl">Organizer</label>
                                <input class="inp" name="organizer" value="{{ old('organizer', $event->organizer) }}">
                            </div>
                            <div>
                                <label class="lbl">Contact email</label>
                                <input type="email" class="inp" name="contactEmail"
                                    value="{{ old('contactEmail', $event->contactEmail) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="lbl">Timezone</label>
                                <select name="timezone" class="inp">
                                    @foreach(['Asia/Jakarta' => 'Asia/Jakarta (WIB, UTC+7)', 'Asia/Makassar' => 'Asia/Makassar (WITA, UTC+8)', 'Asia/Jayapura' => 'Asia/Jayapura (WIT, UTC+9)'] as $tz => $tzLabel)
                                        <option value="{{ $tz }}" @selected(old('timezone', $event->timezone) === $tz)>
                                            {{ $tzLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="lbl">Currency</label>
                                <select name="currency" class="inp">
                                    <option value="IDR" selected>IDR · Indonesian rupiah</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div x-show="tab === 'settings'" x-cloak class="card p-6">
                    <h2 class="text-lg font-semibold">Registration &amp; deadlines</h2>
                    <p class="text-sm text-gray-500 mt-1 mb-5">Times follow the event timezone. The event can only be
                        published when both registration dates are set.</p>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="lbl">Registration opens</label>
                            <input type="datetime-local" class="inp" name="registrationOpenAt"
                                value="{{ old('registrationOpenAt', $dt($event->registrationOpenAt)) }}">
                        </div>
                        <div>
                            <label class="lbl">Registration closes</label>
                            <input type="datetime-local" class="inp" name="registrationCloseAt"
                                value="{{ old('registrationCloseAt', $dt($event->registrationCloseAt)) }}">
                        </div>
                        <div>
                            <label class="lbl">BIB transfer deadline</label>
                            <input type="datetime-local" class="inp" name="transferDeadline"
                                value="{{ old('transferDeadline', $dt($event->transferDeadline)) }}">
                            <p class="text-xs text-gray-500 mt-1.5">Runners can transfer their BIB until this date.</p>
                        </div>
                        <div>
                            <label class="lbl">Category change deadline</label>
                            <input type="datetime-local" class="inp" name="categoryChangeDeadline"
                                value="{{ old('categoryChangeDeadline', $dt($event->categoryChangeDeadline)) }}">
                            <p class="text-xs text-gray-500 mt-1.5">Runners can upgrade or downgrade until this date.</p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Kolom kanan -->
            <div x-show="tab === 'info'" x-cloak class="space-y-5">
                <div class="card p-6">
                    <h3 class="text-lg font-semibold mb-4">Publication</h3>
                    @if($isNew)
                        <p class="text-sm text-gray-500">Save the event to get its public page. New events start as a draft.</p>
                    @else
                        @include('admin.partials.status-badge', ['status' => $event->status])
                        <p class="text-sm text-gray-500 mt-3">{{ $statusText[$event->status] ?? '' }}</p>

                        <label class="lbl mt-5">Event URL</label>
                        <input class="inp bg-gray-50 text-gray-600" readonly value="{{ url('/events/' . $event->slug) }}">
                        <a href="{{ url('/events') }}" target="_blank" class="btn-outline w-full mt-4">
                            <i class="ph ph-arrow-square-out"></i> Open registration page
                        </a>

                        @foreach($actions[$event->status] ?? [] as [$act, $text, $cls])
                            <form method="POST" action="{{ route('admin.events.status', $eventId) }}" class="mt-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="{{ $act }}">
                                <button type="submit" class="{{ $cls }} w-full">{{ $text }}</button>
                            </form>
                        @endforeach
                    @endif
                </div>

                <div class="rounded-2xl bg-[#e8f1ea] p-6">
                    <h3 class="font-semibold mb-4">Ready for the start line</h3>
                    @php
                        $checks = [
                            ['Event information complete', $ready['info']],
                            [$ready['categories'] . ' ' . ($ready['categories'] === 1 ? 'category' : 'categories') . ' configured', $ready['categories'] > 0],
                            ['Registration dates set', $ready['dates']],
                        ];
                    @endphp
                    <ul class="space-y-3 text-sm">
                        @foreach($checks as [$text, $ok])
                            <li class="flex items-center gap-2.5 {{ $ok ? 'text-gray-900' : 'text-gray-400' }}">
                                <i class="ph {{ $ok ? 'ph-check-circle text-forest-700' : 'ph-circle' }} text-lg"></i>
                                {{ $text }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- TAB CATEGORIES -->
        @include('admin.events._categories')
    </div>

    <script>
        function eventPage(initialTab, eventDate) {
            return {
                tab: initialTab,
                modal: false,
                eventDate: eventDate,
                form: {},
                blank() {
                    return {
                        id: null, name: '', distanceKm: '', elevationGain: '', cutOffHours: '',
                        quota: '', price_local: '', price_foreigner: '', benefits: '',
                        qualification_required: false, qualification_min_distance: '', qualification_years: 2,
                        registered: 0,
                    };
                },
                setTab(t) {
                    this.tab = t;
                    const url = new URL(window.location);
                    url.searchParams.set('tab', t);
                    history.replaceState(null, '', url);
                },
                openCreate() { this.form = this.blank(); this.modal = true; },
                openEdit(c) { this.form = Object.assign(this.blank(), c); this.modal = true; },
            };
        }
    </script>
@endsection