<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BibTransfer extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bib_transfers';
    protected $fillable = ['registration_id', 'from_user_id', 'to_user_id', 'status', 'respondedAt'];
    protected $casts = ['respondedAt' => 'datetime'];
}