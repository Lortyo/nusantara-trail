<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Profile extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'user_id', 'fullName', 'bibName', 'gender', 'dateOfBirth', 'bloodType',
        'nationality', 'city', 'country', 'address', 'postalCode', 'phone',
        'itraId', 'utmbId', 'identity', 'emergencyContact',
    ];

    protected $casts = ['dateOfBirth' => 'datetime'];
}