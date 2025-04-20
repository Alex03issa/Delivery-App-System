<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'lat',
        'lng',
        'status',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_id');
    }
}
