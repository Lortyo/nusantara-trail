<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class CategoryChange extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['registration_id','user_id','event_id','from_category_id','to_category_id','newPrice','priceDifference','adminFee','total','certificatePath','status','expiresAt'];
    protected $casts = ['expiresAt' => 'datetime', 'newPrice' => 'integer', 'priceDifference' => 'integer', 'adminFee' => 'integer', 'total' => 'integer'];
}