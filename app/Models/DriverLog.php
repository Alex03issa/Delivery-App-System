<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'delivery_id',
        'status',
        'note',
    ];

    // Relationships

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }

    public function strike()
    {
        return $this->hasOne(DriverStrike::class, 'driver_log_id');
    }
}
