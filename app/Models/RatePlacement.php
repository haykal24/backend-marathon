<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'slot_key',
        'notes',
        'is_active',
        'order_column',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_column' => 'integer',
    ];

    public function packages()
    {
        return $this->hasMany(RatePackage::class, 'rate_placement_id');
    }
}
