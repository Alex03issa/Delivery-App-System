<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'external_chat_id',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }
}
