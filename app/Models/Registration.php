<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Registration extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['user_id','event_id','category_id','registrationCode','jerseySize','status','totalAmount','expiresAt','history'];
    protected $casts = ['expiresAt' => 'datetime', 'totalAmount' => 'integer', 'history' => 'array'];
}