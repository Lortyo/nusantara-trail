@extends('layouts.admin')
@section('title', 'Qualifications')
@section('breadcrumb', 'Qualifications')

@section('content')
@if(! $event)
    <div class="card p-10 text-sm text-gray-500">No events yet.</div>
@else
@php
    $chip = ['pending' => ['Pending review', 'bg-[#fdf0d5] text-[#a8670f]'], 'approved' => ['Approved', 'bg-[#e3f0e8] text-forest-700'], 'rejected' => ['Rejected', 'bg-[#fde8e8] text-[#b42318]']];
    $label = ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'all' => ''];
@endphp

<div x-data="qualPage(@js($rows), {{ request()->boolean('next') ? 'true' : 'false' }})" @keydown.escape.window="close()">

    <h1 class="text-[34px] font-semibold tracking-tight text-forest-900">Qualification review</h1>
    <form method="GET" action="/admin/qualifications" class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <select name="event" onchange="this.form.submit()" class="bg-transparent font-medium focus:outline-none cursor-pointer">
            @foreach($events as $e)<option value="{{ $e->getKey() }}" @selected((string) $e->getKey() === (string) $event->getKey())>{{ $e->name }}</option>@endforeach
        </select>
        <span>· Review experience before the start line.</span>
    </form>

    <div class="flex flex-wrap items-center gap-2 mt-6 text-[11px]">
        <span class="chip bg-[#fdf0d5] text-[#a8670f]">{{ $counts['pending'] }} pending review</span>
        <span class="chip bg-[#e3f0e8] text-forest-700">{{ $counts['approved'] }} approved</span>
        <span class="chip bg-[#fde8e8] text-[#b42318]">{{ $counts['rejected'] }} rejected</span>
        <span class="text-gray-500 text-xs ml-1">{{ $oldestDays === null ? 'No pending requests' : 'Oldest: ' . $oldestDays . ' ' . ($oldestDays === 1 ? 'day' : 'days') . ' ago' }}</span>
    </div>

    <div class="card mt-5">
        <form method="GET" action="/admin/qualifications" class="p-5 space-y-3">
            <input type="hidden" name="event" value="{{ $event->getKey() }}">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Search runner or request..." class="inp pl-11">
            </div>
            <div class="flex gap-3">
                <select name="status" onchange="this.form.submit()" class="inp w-auto h-10 pr-8">
                    <option value="pending" @selected($status === 'pending')>Status: Pending review</option>
                    <option value="approved" @selected($status === 'approved')>Status: Approved</option>
                    <option value="rejected" @selected($status === 'rejected')>Status: Rejected</option>
                    <option value="all" @selected($status === 'all')>Status: All</option>
                </select>
                <select name="category" onchange="this.form.submit()" class="inp w-auto h-10 pr-8">
                    <option value="">All categories</option>
                    @foreach($qualCats as $c)<option value="{{ $c->getKey() }}" @selected(request('category') === (string) $c->getKey())>{{ rtrim(rtrim(number_format($c->distanceKm, 1, '.', ''), '0'), '.') }}K {{ $c->name }}</option>@endforeach
                </select>
            </div>
        </form>

        <table class="w-full">
            <thead class="bg-[#f8f7f2]"><tr><th class="th">Runner</th><th class="th">Category</th><th class="th">Claimed race</th><th class="th">Status</th></tr></thead>
            <tbody class="divide-y divide-[#eceae2]">
                @forelse($rows as $i => $r)
                    <tr class="cursor-pointer hover:bg-[#faf9f5]" @click="open(rows[{{ $i }}])">
                        <td class="td"><p class="font-semibold">{{ $r['runner'] }}</p><p class="text-xs text-gray-500">{{ $r['code'] }} · {{ $r['submittedShort'] }}</p></td>
                        <td class="td">{{ $r['category'] }}</td>
                        <td class="td"><p class="font-semibold">{{ $r['race'] }}</p><p class="text-xs text-gray-500">{{ $r['distance'] }} km · {{ $r['raceDate'] }}</p></td>
                        <td class="td"><span class="chip {{ $chip[$r['status']][1] }}">{{ $chip[$r['status']][0] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No qualification requests found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="flex items-center justify-between px-6 py-4 border-t border-[#eceae2] text-sm text-gray-500">
            <span>Showing {{ $page->count() }} of {{ $page->total() }} {{ $label[$status] ?? '' }} requests</span>
            @if($page->hasPages())
                <div class="flex items-center gap-3">
                    @if(! $page->onFirstPage())<a href="{{ $page->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>@endif
                    @foreach($page->getUrlRange(1, $page->lastPage()) as $n => $url)<a href="{{ $url }}" class="{{ $n == $page->currentPage() ? 'font-bold text-gray-900' : '' }}">{{ $n }}</a>@endforeach
                    @if($page->hasMorePages())<a href="{{ $page->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>@endif
                </div>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <p class="text-sm text-gray-500">Select a runner to view their proof of finish.</p>
        <button type="button" @click="next()" class="btn-primary"><i class="ph ph-arrow-right"></i> Review next</button>
    </div>
    <p class="text-sm text-gray-500 mt-6">Approving a qualification does not confirm payment. Runners are notified of every review decision by email.</p>

    <!-- DRAWER -->
    <div x-show="selected" x-cloak class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-forest-900/30" @click="close()"></div>
        <aside class="absolute right-0 top-0 h-full w-[480px] max-w-full bg-white shadow-2xl flex flex-col">
            <template x-if="selected">
                <div class="flex flex-col h-full min-h-0">
                    <div class="flex items-start justify-between px-8 py-6 border-b border-[#eceae2]">
                        <div>
                            <h2 class="text-2xl font-semibold">Review qualification</h2>
                            <p class="text-sm text-gray-500 mt-1">Request <span x-text="selected.code"></span> · Submitted <span x-text="selected.submitted"></span></p>
                        </div>
                        <button type="button" @click="close()" class="text-gray-500 hover:text-gray-900"><i class="ph ph-x text-2xl"></i></button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold" x-text="selected.runner"></p>
                                <p class="text-xs text-gray-500">BIB <span x-text="selected.bib"></span> · <span x-text="selected.category"></span></p>
                            </div>
                            <span class="chip" :class="{'bg-[#fdf0d5] text-[#a8670f]': selected.status==='pending', 'bg-[#e3f0e8] text-forest-700': selected.status==='approved', 'bg-[#fde8e8] text-[#b42318]': selected.status==='rejected'}"
                                  x-text="selected.status==='pending' ? 'Pending review' : (selected.status==='approved' ? 'Approved' : 'Rejected')"></span>
                        </div>

                        <div class="rounded-xl bg-[#e8f1ea] px-4 py-3">
                            <p class="font-semibold text-sm">Requirement: <span x-text="selected.requirement"></span></p>
                            <p class="text-xs text-gray-500 mt-1" x-show="selected.cutoff">Race finish must be on or after <span x-text="selected.cutoff"></span>.</p>
                        </div>

                        <div>
                            <h3 class="font-semibold">Claimed race details</h3>
                            <dl class="mt-3 space-y-2.5 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Race</dt><dd class="font-semibold" x-text="selected.race"></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Date / Distance</dt><dd class="font-semibold"><span x-text="selected.raceDate"></span> / <span x-text="selected.distance"></span> km</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Finish time / BIB</dt><dd class="font-semibold"><span x-text="selected.finish"></span> / <span x-text="selected.raceBib"></span></dd></div>
                                <div class="flex justify-between" x-show="selected.resultUrl"><dt class="text-gray-500">Result link</dt><dd><a :href="selected.resultUrl" target="_blank" class="font-semibold text-forest-700 underline">Open</a></dd></div>
                            </dl>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">Proof of finish</h3>
                                <a :href="selected.proofUrl" target="_blank" class="text-gray-500 hover:text-gray-900"><i class="ph ph-arrows-out-simple text-lg"></i></a>
                            </div>
                            <div class="mt-3 rounded-2xl border border-[#e2e0d8] bg-[#f4f3ee] p-3">
                                <template x-if="selected.proofIsImage"><img :src="selected.proofUrl" class="w-full rounded-lg bg-white"></template>
                                <template x-if="!selected.proofIsImage"><iframe :src="selected.proofUrl" class="w-full h-[320px] rounded-lg bg-white"></iframe></template>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-2"><i class="ph ph-file text-base"></i><span x-text="selected.proofName"></span> · <span x-text="selected.proofSize"></span></p>
                        </div>

                        <!-- Sudah direview -->
                        <div x-show="selected.status !== 'pending'" class="rounded-xl bg-[#f4f3ee] px-4 py-3 text-sm">
                            <p class="font-semibold">Review note</p>
                            <p class="text-gray-600 mt-1" x-text="selected.note || 'No note.'"></p>
                        </div>
                    </div>

                    <!-- Form keputusan -->
                    <form x-show="selected.status === 'pending'" method="POST" :action="'/admin/qualifications/' + selected.id" class="border-t border-[#eceae2] px-8 py-5">
                        @csrf @method('PATCH')
                        <label class="text-sm font-semibold">Review note</label>
                        <textarea name="note" rows="3" x-model="note" class="inp h-auto py-3 mt-2" placeholder="Distance and race date meet the requirement."></textarea>
                        <p class="text-xs text-gray-500 mt-1.5">Required when rejecting. The note is sent to the runner.</p>
                        <div class="flex gap-3 mt-4">
                            <button type="submit" name="decision" value="approve" class="btn-primary"><i class="ph ph-check"></i> Approve</button>
                            <button type="submit" name="decision" value="reject" class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#b42318] hover:bg-[#9a1f15] text-white text-sm font-semibold"><i class="ph ph-x"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </template>
        </aside>
    </div>
</div>

<script>
    function qualPage(rows, autoOpen) {
        return {
            rows, selected: null, note: '',
            open(r) { this.selected = r; this.note = r.note || ''; },
            close() { this.selected = null; },
            next() {
                const pending = this.rows.filter(r => r.status === 'pending');
                if (!pending.length) return;
                const i = this.selected ? pending.findIndex(r => r.id === this.selected.id) : -1;
                this.open(pending[(i + 1) % pending.length]);
            },
            init() { if (autoOpen) this.next(); },
        };
    }
</script>
@endif
@endsection