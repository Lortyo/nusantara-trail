<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Qualification extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['user_id','event_id','category_id','requestCode','raceName','raceDate','distanceKm','finishTime','raceBib','resultUrl','proofPath','proofName','status','reviewNote','reviewedBy','reviewedAt'];
    protected $casts = ['raceDate' => 'datetime', 'reviewedAt' => 'datetime', 'distanceKm' => 'float'];
}