<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RaceEvent extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'race_events';

    protected $fillable = [
        'name',
        'location',
        'date',
        'description',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
