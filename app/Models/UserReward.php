<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'points_earned',
        'points_redeemed',
        'current_balance',
        'last_earned_at',
        'last_redeemed_at',
        'status',
    ];

    protected $casts = [
        'last_earned_at' => 'datetime',
        'last_redeemed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Auto-calculate balance before saving
    protected static function booted()
    {
        static::saving(function ($reward) {
            $reward->current_balance = $reward->points_earned - $reward->points_redeemed;
        });
    }

    // Optional helper: calculate balance at runtime (doesn't save)
    public function calculateBalance(): int
    {
        return $this->points_earned - $this->points_redeemed;
    }
}
