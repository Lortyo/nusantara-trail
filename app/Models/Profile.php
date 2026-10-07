<?php

namespace App\Models;

// Menggunakan Eloquent khusus MongoDB
use MongoDB\Laravel\Eloquent\Model;

class Profile extends Model
{
    // Tentukan koneksi mongodb jika diatur spesifik di config/database.php
    protected $connection = 'mongodb';
    protected $collection = 'profiles';

    protected $fillable = [
        'user_id',
        'fullName',
        'bibName',
        'gender',
        'dateOfBirth',
        'bloodType',
        'nationality',
        'phone',
        'address',
        'city',
        'country',
        'postalCode',
        'identity_type',
        'identity_number',
        'itraId',
        'utmbId',
        'emergency_name',
        'emergency_phone',
        'emergency_relationship',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}