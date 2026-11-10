@extends('layouts.admin')

@section('title', 'Events')
@section('breadcrumb', 'Events')

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-[28px] font-semibold tracking-tight text-forest-900">Events</h1>
        <p class="text-sm text-gray-500 mt-1">Plan the route. Open the gates. Manage every race in one place.</p>
        <div class="flex flex-wrap gap-2 mt-4">
            <span class="chip bg-white border border-[#e2e0d8] text-gray-600">{{ $counts['total'] }} {{ $counts['total'] === 1 ? 'event' : 'events' }}</span>
            <span class="chip bg-[#e3f0e8] text-forest-700">{{ $counts['open'] }} registration open</span>
            <span class="chip bg-[#fdf0d5] text-[#a8670f]">{{ $counts['draft'] }} draft</span>
        </div>
    </div>
    <a href="{{ route('admin.events.create') }}" class="btn-primary"><i class="ph ph-plus"></i> Create event</a>
</div>

<div class="card mt-6">
    <form method="GET" action="{{ route('admin.events.index') }}" class="flex items-center justify-between gap-3 p-4">
        <div class="relative w-full max-w-[320px]">
            <i class="ph ph-magnifying-glass absolute left-3.5 top-3 text-gray-400"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search events..." class="inp pl-10 h-10">
        </div>
        <div class="flex gap-3">
            <select name="status" onchange="this.form.submit()" class="inp h-10 w-auto pr-8">
                <option value="">All statuses</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="open" @selected(request('status') === 'open')>Registration open</option>
                <option value="closed" @selected(request('status') === 'closed')>Registration closed</option>
                <option value="finished" @selected(request('status') === 'finished')>Completed</option>
            </select>
            <select name="season" onchange="this.form.submit()" class="inp h-10 w-auto pr-8">
                <option value="">All seasons</option>
                @foreach(range(now()->year + 1, now()->year - 2) as $y)
                    <option value="{{ $y }}" @selected((int) request('season') === $y)>Season: {{ $y }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <table class="w-full">
        <thead class="bg-[#f8f7f2]">
            <tr>
                <th class="th">Event</th>
                <th class="th">Race date</th>
                <th class="th">Categories</th>
                <th class="th">Registrations</th>
                <th class="th">Status</th>
                <th class="th">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#eceae2]">
            @forelse($events as $event)
                @php $id = (string) $event->getKey(); $s = $stats[$id]; @endphp
                <tr>
                    <td class="td">
                        <a href="{{ route('admin.events.edit', $id) }}" class="font-semibold hover:underline">{{ $event->name }}</a>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ collect([data_get($event, 'location.city'), data_get($event, 'location.province')])->filter()->implode(', ') ?: '-' }}
                        </p>
                    </td>
                    <td class="td">{{ optional($event->eventDate)->format('d M Y') ?? '-' }}</td>
                    <td class="td">{{ $s['label'] }}</td>
                    <td class="td">{{ $s['taken'] }} / {{ $s['total'] }}</td>
                    <td class="td">@include('admin.partials.status-badge', ['status' => $event->status])</td>
                    <td class="td">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.events.edit', $id) }}" class="font-semibold text-forest-700 hover:underline">Manage</a>
                            <div x-data="{ open: false }" class="relative">
                                <button type="button" @click="open = !open" class="text-gray-400 hover:text-gray-700">
                                    <i class="ph ph-dots-three-bold text-lg"></i>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false"
                                     class="absolute right-0 mt-1 w-44 bg-white border border-[#e9e7df] rounded-xl shadow-lg z-20 py-1">
                                    <form method="POST" action="{{ route('admin.events.destroy', $id) }}"
                                          onsubmit="return confirm('Delete this event and all its categories?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete event</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No events found. Click "Create event" to add the first one.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="flex items-center justify-between px-6 py-4 border-t border-[#eceae2] text-sm text-gray-500">
        <span>Showing {{ $events->count() }} of {{ $events->total() }} events</span>
        @if($events->hasPages())
            <div class="flex items-center gap-3">
                @if($events->onFirstPage())
                    <span class="text-gray-300"><i class="ph ph-caret-left"></i></span>
                @else
                    <a href="{{ $events->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                @endif
                @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                    <a href="{{ $url }}" class="{{ $page == $events->currentPage() ? 'font-bold text-gray-900' : '' }}">{{ $page }}</a>
                @endforeach
                @if($events->hasMorePages())
                    <a href="{{ $events->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                @else
                    <span class="text-gray-300"><i class="ph ph-caret-right"></i></span>
                @endif
            </div>
        @endif
    </div>
</div>

<p class="mt-6 flex items-center gap-2 text-xs text-gray-500">
    <i class="ph ph-question text-base"></i>
    Draft events are only visible to your team. Publish an event when categories and registration dates are ready.
</p>
@endsection