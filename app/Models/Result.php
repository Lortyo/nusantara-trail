<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Result extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['registration_id','finishTime','overallRank','categoryRank','status'];
}