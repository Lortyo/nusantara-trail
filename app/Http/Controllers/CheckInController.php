<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PicksEvent;
use App\Models\Profile;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    use PicksEvent;

    private function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }

    private function kits(string $eid): int
    {
        return Ticket::where('event_id', $eid)->whereNotNull('kitCollectedAt')->count();
    }

    private function describe(Ticket $t, ?Registration $reg = null): array
    {
        $reg ??= Registration::find($t->registration_id);
        $u = $reg ? User::find($reg->user_id) : null;
        $p = $reg ? Profile::where('user_id', (string) $reg->user_id)->first() : null;
        $cat = $reg ? RaceCategory::find($reg->category_id) : null;

        return [
            'name' => optional($p)->fullName ?? optional($u)->name ?? '-',
            'bib' => $t->bibNumber,
            'category' => $cat ? $this->km($cat->distanceKm) . 'K ' . $cat->name : '-',
            'jersey' => optional($reg)->jerseySize ?? '-',
        ];
    }

    private function item(Ticket $t, string $type, $at, ?string $by): array
    {
        $d = $this->describe($t);

        return ['name' => $d['name'], 'bib' => $d['bib'], 'category' => $d['category'],
            'time' => $at->format('H:i'), 'type' => $type, 'by' => $by, 'ts' => $at->timestamp];
    }

    private function history(string $eid): array
    {
        $items = collect();
        foreach ([['kitCollectedAt', 'kitCollectedBy', 'Kit'], ['checkedInAt', 'checkedInBy', 'Start']] as [$at, $by, $type]) {
            Ticket::where('event_id', $eid)->whereNotNull($at)->orderBy($at, 'desc')->limit(30)->get()
                ->each(fn ($t) => $items->push($this->item($t, $type, $t->{$at}, $t->{$by})));
        }

        return $items->sortByDesc('ts')->take(30)->values()->all();
    }

    private function reply(string $state, string $title, string $eid, array $extra = [])
    {
        return response()->json(array_merge([
            'ok' => $state === 'success', 'state' => $state, 'title' => $title,
            'name' => null, 'line' => null, 'detail' => null, 'detail2' => null,
            'kits' => $this->kits($eid), 'item' => null,
        ], $extra));
    }

    public function index(Request $request)
    {
        [$events, $event] = $this->pickEvent($request);
        if (! $event) return view('admin.checkin', ['event' => null, 'events' => $events]);

        $eid = (string) $event->getKey();

        return view('admin.checkin', [
            'events' => $events, 'event' => $event,
            'cfg' => ['event' => $eid, 'kits' => $this->kits($eid), 'history' => $this->history($eid), 'csrf' => csrf_token()],
        ]);
    }

    // Dipanggil dari scanner (QR token) atau input BIB manual
    public function scan(Request $request)
    {
        $d = $request->validate([
            'event' => 'required|string',
            'code' => 'required|string|max:100',
            'mode' => 'required|in:kit,start',
        ]);
        $eid = $d['event'];
        $code = trim($d['code']);
        $isKit = $d['mode'] === 'kit';

        $ticket = Ticket::where('qrToken', $code)->first()
            ?? Ticket::where('event_id', $eid)->where('bibNumber', $code)->first();

        if (! $ticket) {
            return $this->reply('invalid', 'Not valid', $eid, ['line' => 'No runner found for this QR code or BIB number.']);
        }

        if ((string) $ticket->event_id !== $eid) {
            return $this->reply('invalid', 'Wrong event', $eid, ['line' => 'This runner is registered for another event.']);
        }

        $reg = Registration::find($ticket->registration_id);
        if (! $reg || $reg->status !== 'paid') {
            return $this->reply('invalid', 'Not valid', $eid, ['line' => 'The registration is not paid.']);
        }

        $info = $this->describe($ticket, $reg);
        $line = 'BIB ' . $info['bib'] . ' · ' . $info['category'] . ' · Jersey ' . $info['jersey'];

        if (! $isKit && ! $ticket->kitCollectedAt) {
            return $this->reply('invalid', 'Kit not collected', $eid, [
                'name' => $info['name'], 'line' => $line, 'detail' => 'Collect the race kit first, then check in for the start.',
            ]);
        }

        [$at, $by] = $isKit ? ['kitCollectedAt', 'kitCollectedBy'] : ['checkedInAt', 'checkedInBy'];
        $staff = $request->user()->name;

        // Atomik: hanya satu scan yang berhasil mengisi waktu (QR hanya bisa dipakai sekali)
        $changed = Ticket::where('_id', $ticket->getKey())->whereNull($at)->update([$at => now(), $by => $staff]);
        $t = $ticket->fresh();

        if (! $changed) {
            return $this->reply('already', $isKit ? 'Already collected' : 'Already checked in', $eid, [
                'name' => $info['name'], 'line' => $line,
                'detail' => 'First recorded ' . $t->{$at}->format('d M, H:i'),
                'detail2' => $t->{$by} ? 'By ' . $t->{$by} : null,
            ]);
        }

        return $this->reply('success', $isKit ? 'Valid, kit collected' : 'Valid, checked in', $eid, [
            'name' => $info['name'], 'line' => $line,
            'detail' => ($isKit ? 'Kit issued successfully' : 'Check-in recorded') . ' · ' . $t->{$at}->format('H:i'),
            'detail2' => $isKit ? 'BIB and jersey collected.' : 'Runner is cleared for the start.',
            'item' => $this->item($t, $isKit ? 'Kit' : 'Start', $t->{$at}, $staff),
        ]);
    }
}