<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'reward_type',
        'threshold',
        'points_given',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
