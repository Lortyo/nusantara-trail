<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Registration extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'registrations';

    protected $fillable = [
        'user_id',
        'event_id',
        'category_id',
        'registrationCode',
        'jerseySize',
        'status',
        'totalAmount',
        'expiresAt',
        'history',
    ];

    protected $casts = [
        'totalAmount' => 'integer',
        'expiresAt' => 'datetime',
        'history' => 'array',
    ];
}