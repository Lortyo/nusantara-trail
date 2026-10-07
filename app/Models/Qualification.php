<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Qualification extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['user_id','event_id','category_id','raceName','raceDate','distanceKm','resultUrl','proofPath','status','reviewNote','reviewedBy','reviewedAt'];
}