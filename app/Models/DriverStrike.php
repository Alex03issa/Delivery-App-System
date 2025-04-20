<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverStrike extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'reason',
        'review_id',
        'issued_by',
        'severity_level',
        'is_resolved',
        'driver_log_id',
    ];

    // Relationships

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function log()
    {
        return $this->belongsTo(DriverLog::class, 'driver_log_id');
    }
}
