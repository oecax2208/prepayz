<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersFaspay extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'cart',
        'payment_method',
        'currency_sign',
        'tax',
        'transaction_number',
        'order_status',
        'shipping_info',
        'billing_info',
        'payment_status',
        'billing_address',
    ];

    protected $casts = [
        'cart' => 'json',
        'shipping_info' => 'json',
        'billing_info' => 'json',
        'billing_address' => 'json',
    ];
}
