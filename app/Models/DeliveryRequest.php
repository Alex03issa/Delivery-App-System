<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'pickup_contact_name',
        'pickup_contact_phone',
        'dropoff_contact_name',
        'dropoff_contact_phone',
        'length_cm',
        'width_cm',
        'height_cm',
        'package_volume',
        'package_weight',
        'package_size',
        'distance_km',
        'extra_charge',
        'price',
        'urgency_level',
        'status',
        'delivery_date',
        'assigned_at',
        'completed_at',
        'payment_method',
        'is_paid',
        'note',
        'tracking_code',
        'scheduled_pickup_at',
        'cancellation_reason'
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_pickup_at' => 'datetime',
        'is_paid' => 'boolean',
        'extra_charge' => 'decimal:2',
        'price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'package_weight' => 'decimal:2',
        'package_volume' => 'decimal:2',
    ];

    // Auto-generate tracking code when creating
    protected static function booted()
    {
        static::creating(function ($delivery) {
            if (!$delivery->tracking_code) {
                $prefix = 'DLV-' . now()->format('Ymd') . '-';
                $delivery->tracking_code = $prefix . strtoupper(Str::random(6));
            }
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'delivery_id');
    }

    public function locations()
    {
        return $this->hasMany(DeliveryLocation::class);
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function pickupLocation()
    {
        return $this->hasOne(Location::class)->where('type', 'pickup');
    }

    public function dropoffLocation()
    {
        return $this->hasOne(Location::class)->where('type', 'dropoff');
    }

}

