<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delivery_request_id',
        'type',
        'address',
        'lat',
        'lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class);
    }
}
