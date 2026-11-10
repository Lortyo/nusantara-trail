<?php

namespace App\Http\Controllers\Concerns;

use App\Models\RaceEvent;
use Illuminate\Http\Request;

trait PicksEvent
{
    protected function pickEvent(Request $request): array
    {
        $events = RaceEvent::orderBy('eventDate', 'desc')->get();
        $wanted = $request->query('event');

        $event = $events->first(fn ($e) => (string) $e->getKey() === $wanted)
            ?? $events->first(fn ($e) => $e->status === 'open')
            ?? $events->first();

        return [$events, $event];
    }
}