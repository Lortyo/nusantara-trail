@extends('layouts.admin')
@section('title', 'Participants')
@section('breadcrumb', 'Participants')

@section('content')
@if(! $event)
    <div class="card p-10 text-sm text-gray-500">No events yet.</div>
@else
<div x-data="partPage(@js($rows))" @keydown.escape.window="closeAll()">

    <h1 class="text-[34px] font-semibold tracking-tight text-forest-900">Participants</h1>
    <form method="GET" action="/admin/participants" class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <select name="event" onchange="this.form.submit()" class="bg-transparent font-medium focus:outline-none cursor-pointer">
            @foreach($events as $e)<option value="{{ $e->getKey() }}" @selected((string) $e->getKey() === (string) $event->getKey())>{{ $e->name }}</option>@endforeach
        </select>
        <span>· Manage runners, payments and race kits.</span>
    </form>

    <div class="flex flex-wrap gap-2 mt-6">
        <span class="chip bg-white border border-[#e2e0d8] text-gray-600">{{ $stats['registered'] }} registered</span>
        <span class="chip bg-[#e3f0e8] text-forest-700">{{ $stats['paid'] }} paid</span>
        <span class="chip bg-[#fdf0d5] text-[#a8670f]">{{ $stats['pending'] }} awaiting payment</span>
        <span class="chip bg-[#e3f0e8] text-forest-700">{{ $stats['kits'] }} kits collected</span>
    </div>

    <div class="card mt-5">
        <form method="GET" action="/admin/participants" class="p-5 space-y-3">
            <input type="hidden" name="event" value="{{ $event->getKey() }}">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Search name, BIB or email..." class="inp pl-11">
            </div>
            <div class="flex gap-3">
                <select name="category" onchange="this.form.submit()" class="inp w-auto h-10 pr-8">
                    <option value="">All categories</option>
                    @foreach($cats as $c)<option value="{{ $c->getKey() }}" @selected(request('category') === (string) $c->getKey())>{{ rtrim(rtrim(number_format($c->distanceKm, 1, '.', ''), '0'), '.') }}K {{ $c->name }}</option>@endforeach
                </select>
                <select name="payment" onchange="this.form.submit()" class="inp w-auto h-10 pr-8">
                    <option value="">Payment: All</option>
                    <option value="paid" @selected(request('payment') === 'paid')>Payment: Paid</option>
                    <option value="pending" @selected(request('payment') === 'pending')>Payment: Awaiting payment</option>
                </select>
                <select name="kit" onchange="this.form.submit()" class="inp w-auto h-10 pr-8">
                    <option value="">Kit status: All</option>
                    <option value="collected" @selected(request('kit') === 'collected')>Kit status: Collected</option>
                    <option value="not" @selected(request('kit') === 'not')>Kit status: Not collected</option>
                </select>
            </div>
        </form>

        <table class="w-full">
            <thead class="bg-[#f8f7f2]"><tr><th class="th">Name</th><th class="th">BIB</th><th class="th">Category</th><th class="th">Payment status</th><th class="th">Kit status</th></tr></thead>
            <tbody class="divide-y divide-[#eceae2]">
                @forelse($rows as $i => $r)
                    <tr class="cursor-pointer hover:bg-[#faf9f5]" @click="open(rows[{{ $i }}])">
                        <td class="td"><p class="font-semibold">{{ $r['name'] }}</p><p class="text-xs text-gray-500">{{ $r['country'] }} · {{ $r['jersey'] }}</p></td>
                        <td class="td">{{ $r['bib'] }}</td>
                        <td class="td">{{ $r['category'] }}</td>
                        <td class="td"><span class="chip {{ $r['payment'] === 'paid' ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#fdf0d5] text-[#a8670f]' }}">{{ $r['payment'] === 'paid' ? 'Paid' : 'Awaiting payment' }}</span></td>
                        <td class="td"><span class="chip {{ $r['kit'] ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#f0efea] text-gray-500' }}">{{ $r['kit'] ? 'Collected' : 'Not collected' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No participants found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="flex items-center justify-between px-6 py-4 border-t border-[#eceae2] text-sm text-gray-500">
            <span>Showing {{ $page->firstItem() ?? 0 }}–{{ $page->lastItem() ?? 0 }} of {{ $page->total() }} registrations</span>
            @if($page->hasPages())
                <div class="flex items-center gap-3">
                    @if(! $page->onFirstPage())<a href="{{ $page->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>@endif
                    @foreach($page->getUrlRange(1, $page->lastPage()) as $n => $url)<a href="{{ $url }}" class="{{ $n == $page->currentPage() ? 'font-bold text-gray-900' : '' }}">{{ $n }}</a>@endforeach
                    @if($page->hasMorePages())<a href="{{ $page->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>@endif
                </div>
            @endif
        </div>
    </div>

    <div class="flex gap-3 mt-6">
        <a href="/admin/dashboard/export?event={{ $event->getKey() }}" class="btn-outline"><i class="ph ph-download-simple"></i> Export CSV</a>
        <button type="button" @click="modal = 'add'" class="btn-primary"><i class="ph ph-plus"></i> Add participant</button>
    </div>

    <!-- DRAWER DETAIL -->
    <div x-show="selected" x-cloak class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-forest-900/30" @click="selected = null"></div>
        <aside class="absolute right-0 top-0 h-full w-[480px] max-w-full bg-white shadow-2xl flex flex-col">
            <template x-if="selected">
                <div class="flex flex-col h-full min-h-0">
                    <div class="flex items-start justify-between px-8 py-6 border-b border-[#eceae2]">
                        <div>
                            <h2 class="text-2xl font-semibold">Participant details</h2>
                            <p class="text-sm text-gray-500 mt-1">Registration <span x-text="selected.code"></span> · <span x-text="selected.created"></span></p>
                        </div>
                        <button type="button" @click="selected = null" class="text-gray-500 hover:text-gray-900"><i class="ph ph-x text-2xl"></i></button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-[#e3f0e8] text-forest-700 font-bold text-lg flex items-center justify-center" x-text="selected.initials"></div>
                            <div><p class="text-xl font-semibold" x-text="selected.name"></p><p class="text-sm text-gray-500" x-text="selected.email"></p></div>
                        </div>

                        <dl class="rounded-xl bg-[#f7f6f1] p-4 space-y-2.5 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">BIB number</dt><dd class="font-semibold" x-text="selected.bib"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd class="font-semibold" x-text="selected.category"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Nationality</dt><dd class="font-semibold" x-text="selected.country"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-semibold" x-text="selected.phone"></dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Jersey size</dt><dd class="font-semibold" x-text="selected.jersey"></dd></div>
                        </dl>

                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">Payment</h3>
                                <span class="chip" :class="selected.payment === 'paid' ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#fdf0d5] text-[#a8670f]'" x-text="selected.payment === 'paid' ? 'Paid' : 'Awaiting payment'"></span>
                            </div>
                            <dl class="mt-3 space-y-2.5 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Amount / Rate</dt><dd class="font-semibold"><span x-text="selected.amount"></span> / <span x-text="selected.rate"></span></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Method</dt><dd class="font-semibold" x-text="selected.method"></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Paid on</dt><dd class="font-semibold" x-text="selected.paidAt"></dd></div>
                            </dl>
                            <a x-show="selected.receipt" :href="selected.receipt" target="_blank" class="inline-flex items-center gap-1 text-sm font-semibold text-forest-700 mt-3 hover:underline">View receipt <i class="ph ph-arrow-up-right"></i></a>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">Race kit</h3>
                                <span class="chip" :class="selected.kit ? 'bg-[#e3f0e8] text-forest-700' : 'bg-[#f0efea] text-gray-500'" x-text="selected.kit ? 'Collected' : 'Not collected'"></span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2" x-show="selected.kit"><span x-text="selected.kitAt"></span><span x-show="selected.kitBy"> · Collected by <span x-text="selected.kitBy"></span></span></p>
                            <p class="text-sm mt-2">BIB <span x-text="selected.bib"></span> · Jersey <span x-text="selected.jersey"></span></p>
                        </div>

                        <div class="rounded-xl bg-[#fbf0dc] p-4">
                            <p class="font-semibold flex items-center gap-2"><i class="ph ph-heartbeat text-xl text-[#a8670f]"></i> Emergency contact</p>
                            <dl class="mt-3 space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Name / Relationship</dt><dd class="font-semibold"><span x-text="selected.em.name || '-'"></span><span x-show="selected.em.rel"> / <span x-text="selected.em.rel"></span></span></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-semibold" x-text="selected.em.phone || '-'"></dd></div>
                            </dl>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Qualification</span>
                            <span class="chip bg-[#e3f0e8] text-forest-700" x-text="selected.qualification"></span>
                        </div>
                    </div>

                    <div class="flex gap-3 border-t border-[#eceae2] px-8 py-5">
                        <button type="button" @click="openEdit()" class="btn-primary"><i class="ph ph-pencil-simple"></i> Edit participant</button>
                        <button type="button" @click="modal = 'email'" class="btn-outline"><i class="ph ph-envelope-simple"></i> Send email</button>
                    </div>
                </div>
            </template>
        </aside>
    </div>

    <!-- MODAL: EDIT -->
    <div x-show="modal === 'edit'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-forest-900/50" @click="modal = null"></div>
        <form method="POST" :action="selected ? '/admin/participants/' + selected.id : '#'" class="relative bg-white rounded-3xl w-full max-w-[560px] p-8 shadow-2xl space-y-4">
            @csrf @method('PUT')
            <h2 class="text-2xl font-semibold">Edit participant</h2>
            <div><label class="lbl">Full name</label><input class="inp" name="full_name" x-model="edit.full_name"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl">Phone</label><input class="inp" name="phone" x-model="edit.phone"></div>
                <div><label class="lbl">Nationality</label><select class="inp" name="nationality" x-model="edit.nationality"><option value="id">Indonesia</option><option value="my">Malaysia</option></select></div>
            </div>
            <div><label class="lbl">Jersey size</label><select class="inp" name="jersey_size" x-model="edit.jersey_size">@foreach(['XS','S','M','L','XL','XXL'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select></div>
            <p class="text-xs text-gray-500">Emergency contact</p>
            <div class="grid grid-cols-3 gap-4">
                <div><label class="lbl">Name</label><input class="inp" name="em_name" x-model="edit.em_name"></div>
                <div><label class="lbl">Relationship</label><input class="inp" name="em_relationship" x-model="edit.em_relationship"></div>
                <div><label class="lbl">Phone</label><input class="inp" name="em_phone" x-model="edit.em_phone"></div>
            </div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" @click="modal = null" class="btn-outline">Cancel</button><button type="submit" class="btn-primary">Save changes</button></div>
        </form>
    </div>

    <!-- MODAL: EMAIL -->
    <div x-show="modal === 'email'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-forest-900/50" @click="modal = null"></div>
        <form method="POST" :action="selected ? '/admin/participants/' + selected.id + '/email' : '#'" class="relative bg-white rounded-3xl w-full max-w-[560px] p-8 shadow-2xl space-y-4">
            @csrf
            <h2 class="text-2xl font-semibold">Send email</h2>
            <p class="text-sm text-gray-500">To: <span x-text="selected ? selected.email : ''"></span></p>
            <div><label class="lbl">Subject</label><input class="inp" name="subject"></div>
            <div><label class="lbl">Message</label><textarea class="inp h-auto py-3" rows="6" name="message"></textarea></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" @click="modal = null" class="btn-outline">Cancel</button><button type="submit" class="btn-primary"><i class="ph ph-paper-plane-tilt"></i> Send</button></div>
        </form>
    </div>

    <!-- MODAL: ADD -->
    <div x-show="modal === 'add'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-forest-900/50" @click="modal = null"></div>
        <form method="POST" action="{{ route('admin.participants.store', $event->getKey()) }}" class="relative bg-white rounded-3xl w-full max-w-[560px] p-8 shadow-2xl space-y-4">
            @csrf
            <h2 class="text-2xl font-semibold">Add participant</h2>
            <p class="text-sm text-gray-500">Registers the runner manually and marks the registration as paid (for example offline or sponsored entries).</p>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl">Full name</label><input class="inp" name="name"></div>
                <div><label class="lbl">Email</label><input type="email" class="inp" name="email"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl">Phone</label><input class="inp" name="phone" placeholder="812 3456 7890"></div>
                <div><label class="lbl">Nationality</label><select class="inp" name="nationality"><option value="id">Indonesia</option><option value="my">Malaysia</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="lbl">Category</label><select class="inp" name="category_id">@foreach($cats as $c)<option value="{{ $c->getKey() }}">{{ rtrim(rtrim(number_format($c->distanceKm, 1, '.', ''), '0'), '.') }}K {{ $c->name }} ({{ $c->slotsAvailable }} left)</option>@endforeach</select></div>
                <div><label class="lbl">Jersey size</label><select class="inp" name="jersey_size">@foreach(['XS','S','M','L','XL','XXL'] as $s)<option value="{{ $s }}" @selected($s === 'M')>{{ $s }}</option>@endforeach</select></div>
            </div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" @click="modal = null" class="btn-outline">Cancel</button><button type="submit" class="btn-primary">Add &amp; mark as paid</button></div>
        </form>
    </div>
</div>

<script>
    function partPage(rows) {
        return {
            rows, selected: null, modal: null, edit: {},
            open(r) { this.selected = r; },
            closeAll() { this.modal = null; this.selected = null; },
            openEdit() {
                const r = this.selected;
                this.edit = { full_name: r.name, phone: r.phone === '-' ? '' : r.phone, nationality: r.nat === 'my' ? 'my' : 'id', jersey_size: r.jersey, em_name: r.em.name, em_relationship: r.em.rel, em_phone: r.em.phone };
                this.modal = 'edit';
            },
        };
    }
</script>
@endif
@endsection