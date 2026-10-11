<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'payment_method',
        'amount',
        'payment_status',
        'transaction_id',
        'paid_at',
        'currency_id',
        'platform_fee',
        'driver_share',
        'center_share',
        'converted_amount',
        'conversion_rate',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:10',
        'platform_fee' => 'decimal:2',
        'driver_share' => 'decimal:2',
        'center_share' => 'decimal:2',
        'converted_amount' => 'decimal:10',
        'conversion_rate' => 'decimal:10',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
