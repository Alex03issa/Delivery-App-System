<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function rewards()
    {
        return $this->hasOne(UserReward::class);
    }

    public function calendarIntegration()
    {
        return $this->hasOne(CalendarIntegration::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
