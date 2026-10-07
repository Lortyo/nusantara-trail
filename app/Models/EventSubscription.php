<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class EventSubscription extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['user_id', 'event_id', 'type'];
}