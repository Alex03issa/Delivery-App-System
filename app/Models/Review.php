<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'reviewer_id',
        'reviewed_id',
        'reviewer_role',
        'reviewed_role',
        'rating',
        'comment',
        'is_flagged',
        'flag_reason',
        'flagged_by_type',
        'flagged_by_id',
        'severity_level',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'severity_level' => 'integer',
    ];

    // Relationships
    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewed()
    {
        return $this->belongsTo(User::class, 'reviewed_id');
    }

    public function flaggedBy()
    {
        return $this->belongsTo(User::class, 'flagged_by_id');
    }

    public function strike()
    {
        return $this->hasOne(DriverStrike::class);
    }
}
