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

    /**
     * Mark the payment as paid (called only after the provider confirmed it).
     */
    public function markConfirmed(?string $transactionId = null): void
    {
        if ($this->payment_status === 'confirmed') {
            return;
        }

        $this->update([
            'payment_status' => 'confirmed',
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);

        $this->delivery?->update(['is_paid' => true]);
    }

    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
