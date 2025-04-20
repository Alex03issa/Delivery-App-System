<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageSizeRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'max_volume',
        'max_weight',
        'size_category',
        'active',
    ];

    protected $casts = [
        'max_volume' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
