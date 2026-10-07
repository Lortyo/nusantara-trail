<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RaceCategory extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'categories';

    protected $fillable = [
        'event_id',
        'name',
        'distanceKm',
        'elevationGain',
        'cutoffHours',
        'quota',
        'slotsAvailable',
        'prices',
        'benefits',
    ];

    protected $casts = [
        'distanceKm' => 'float',
        'elevationGain' => 'float',
        'cutoffHours' => 'float',
        'quota' => 'integer',
        'slotsAvailable' => 'integer',
        'prices' => 'array',
        'benefits' => 'array',
    ];
}