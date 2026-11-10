<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RaceCategory extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'event_id', 'name', 'distanceKm', 'elevationGain', 'cutOffHours',
        'quota', 'slotsAvailable', 'price', 'benefits', 'qualification',
    ];

    protected $casts = [
        'distanceKm' => 'float',
        'elevationGain' => 'float',
        'cutOffHours' => 'float',
        'quota' => 'integer',
        'slotsAvailable' => 'integer',
        'price' => 'array',
        'benefits' => 'array',
        'qualification' => 'array',
    ];
}