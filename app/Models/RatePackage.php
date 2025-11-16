<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RatePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'placement_key',
        'rate_category_id',
        'rate_placement_id',
        'description',
        'price',
        'price_display',
        'duration',
        'audience',
        'deliverables',
        'channels',
        'cta_label',
        'cta_url',
        'is_active',
        'order_column',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'deliverables' => 'array',
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $package) {
            if ($package->rateCategory && !$package->category) {
                $package->category = $package->rateCategory->slug;
            }

            if ($package->ratePlacement && !$package->placement_key) {
                $package->placement_key = $package->ratePlacement->slot_key;
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function rateCategory()
    {
        return $this->belongsTo(RateCategory::class);
    }

    public function ratePlacement()
    {
        return $this->belongsTo(RatePlacement::class);
    }
}