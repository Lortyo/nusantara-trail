<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Profile;
use App\Models\RaceCategory;

trait ParticipantHelpers
{
    protected function isLocal(string $uid): bool
    {
        $p = Profile::where('user_id', $uid)->first();
        return strtolower((string) (optional($p)->nationality ?? 'id')) === 'id';
    }

    protected function priceFor(RaceCategory $c, bool $isLocal): int
    {
        return (int) data_get($c, $isLocal ? 'price.local' : 'price.foreigner', 0);
    }

    protected function km($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }
}