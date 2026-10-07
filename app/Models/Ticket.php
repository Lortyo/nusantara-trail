<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Ticket extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['registration_id','event_id','bibNumber','qrToken','kitCollectedAt','checkedInAt','issuedAt'];
    protected $casts = ['kitCollectedAt' => 'datetime', 'checkedInAt' => 'datetime', 'issuedAt' => 'datetime'];
}