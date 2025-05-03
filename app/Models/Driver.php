<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_color',
        'vehicle_year',
        'plate_number',
        'license_number',
        'license_expiry',
        'registration_document',
        'pricing_type',
        'price_per_km',
        'fixed_price',
        'is_approved',
        'availability',
        'earnings',
        'delivery_count',
        'km_completed',
        'rating_average',
        'warning_count',
        'visibility_status',
    ];

    protected $casts = [
        'availability' => 'array',
        'is_approved' => 'boolean',
        'earnings' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'km_completed' => 'decimal:2',
        'rating_average' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }

    public function logs()
    {
        return $this->hasMany(DriverLog::class);
    }

    public function strikes()
    {
        return $this->hasMany(DriverStrike::class);
    }

    public function rewards()
    {
        return $this->hasOne(UserReward::class, 'user_id')->where('role', 'driver');
    }

    public function criticalStrikes()
    {
        return $this->strikes()->where('severity_level', '>=', 3);
    }
    
    public function getStrikeCountAttribute()
    {
        return $this->strikes()->count();
    }

}
