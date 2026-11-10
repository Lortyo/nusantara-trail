<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    // TAMBAHKAN RELASI KE PROFILE
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', '_id');
    }

    // ============ JWT METHODS (REQUIRED) ============

    /**
     * Get the identifier that will be stored in JWT subject claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return custom claims to be added to JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'role' => $this->role ?? 'user',
        ];
    }
}