<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RaceEvent extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'created_by',
        'name',
        'slug',
        'description',
        'location',
        'eventDate',
        'startTime',
        'organizer',
        'contactEmail',
        'timezone',
        'currency',
        'registrationOpenAt',
        'registrationCloseAt',
        'transferDeadline',
        'categoryChangeDeadline',
        'bannerUrl',
        'status',
        'bibSequence',
    ];

    protected $casts = [
        'location' => 'array',
        'eventDate' => 'datetime',
        'registrationOpenAt' => 'datetime',
        'registrationCloseAt' => 'datetime',
        'transferDeadline' => 'datetime',
        'categoryChangeDeadline' => 'datetime',
        'bibSequence' => 'integer',
    ];

    public function stateFor(int $slotsLeft, int $quotaTotal): string
    {
        $now = now();
        if ($this->registrationOpenAt && $now->lt($this->registrationOpenAt))
            return 'opening_soon';
        if ($this->registrationCloseAt && $now->gt($this->registrationCloseAt))
            return 'closed';
        if ($quotaTotal > 0 && $slotsLeft <= 0)
            return 'waitlist';
        if ($quotaTotal > 0 && $slotsLeft <= $quotaTotal * 0.25)
            return 'spots_left';
        return 'open';
    }
}