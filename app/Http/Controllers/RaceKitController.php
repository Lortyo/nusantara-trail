<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\RaceCategory;
use App\Models\RaceEvent;
use App\Models\Registration;
use App\Models\Ticket;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class RaceKitController extends Controller
{
    private function qrDataUri(string $payload): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(320, 1), new SvgImageBackEnd()));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($payload));
    }

    public function show(Request $request)
    {
        $uid = (string) $request->user()->getKey();

        $items = Registration::where('user_id', $uid)->where('status', 'paid')->get()
            ->map(function ($r) {
                $ticket = Ticket::where('registration_id', (string) $r->getKey())->first();
                $event = RaceEvent::find($r->event_id);

                return ($ticket && $event && $event->status !== 'finished')
                    ? ['reg' => $r, 'ticket' => $ticket, 'event' => $event]
                    : null;
            })
            ->filter()
            ->sortBy(fn ($i) => optional($i['event']->eventDate)->timestamp)
            ->values();

        if ($items->isEmpty()) return view('participant.race-kit', ['ticket' => null]);

        $wanted = $request->query('registration');
        $item = $items->first(fn ($i) => (string) $i['reg']->getKey() === $wanted) ?? $items->first();

        $reg = $item['reg'];
        $ticket = $item['ticket'];
        $event = $item['event'];
        $cat = RaceCategory::find($reg->category_id);
        $profile = Profile::where('user_id', $uid)->first();

        $benefits = collect((array) ($cat->benefits ?? []))
            ->reject(fn ($b) => str_contains(mb_strtolower($b), 'jersey'))->values();

        $options = $items->mapWithKeys(fn ($i) => [(string) $i['reg']->getKey() => $i['event']->name])->all();

        return view('participant.race-kit', [
            'reg' => $reg, 'ticket' => $ticket, 'event' => $event, 'cat' => $cat, 'profile' => $profile,
            'benefits' => $benefits, 'options' => $options,
            'qr' => $this->qrDataUri($ticket->qrToken),
        ]);
    }
}