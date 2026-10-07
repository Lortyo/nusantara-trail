<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RaceEvent extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'events';

    protected $fillable = [
        'created_by',
        'slug',
        'name',
        'description',
        'location',
        'eventDate',
        'registrationOpenAt',
        'registrationCloseAt',
        'bannerUrl',
        'status',
        'bibSequence',
    ];

    protected $casts = [
        'eventDate' => 'datetime',
        'registrationOpenAt' => 'datetime',
        'registrationCloseAt' => 'datetime',
        'bibSequence' => 'integer',
    ];
}