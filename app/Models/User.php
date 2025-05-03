<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'otp_code',
        'provider_name',
        'provider_id',
        'profile_picture',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'provider_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationship to Driver (One to One)
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    // Relationship to Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
