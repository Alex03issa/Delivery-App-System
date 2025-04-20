<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // The user who receives the notification.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Mark the notification as read.
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    //Scope for unread notifications.
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
