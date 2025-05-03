<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'day_of_week',
        'start_time',
        'end_time',
        'city',
        'governorate',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
