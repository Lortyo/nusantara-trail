<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['registration_id','change_id','user_id','orderId','purpose','gateway','paymentMethod','transactionId','snapToken','amount','status','paidAt'];
    protected $casts = ['amount' => 'integer', 'paidAt' => 'datetime'];
}