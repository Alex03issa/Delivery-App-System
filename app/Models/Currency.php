<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'rate_to_usd',
    ];

    protected $casts = [
        'rate_to_usd' => 'decimal:4',
    ];


    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
