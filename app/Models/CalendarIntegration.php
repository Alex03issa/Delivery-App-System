<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'token_expiry',
        'sync_enabled',
    ];

    protected $casts = [
        'token_expiry' => 'datetime',
        'sync_enabled' => 'boolean',
    ];

    // Relationship to the User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
