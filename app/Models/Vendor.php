<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vendor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->slug)) {
                $vendor->slug = Str::slug($vendor->name);
            }
        });

        static::updating(function ($vendor) {
            if ($vendor->isDirty('name') && empty($vendor->slug)) {
                $vendor->slug = Str::slug($vendor->name);
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'website',
        'email',
        'phone',
        'city',
        'instagram_handle',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        // Logo collection (single file)
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'))
            ->useFallbackUrl('/images/placeholder.jpg');

        // Gallery collection (multiple files)
        $this->addMediaCollection('gallery')
            ->useDisk(config('media-library.disk_name', 'r2'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // OPTIMASI: Logo vendor - ukuran kecil sudah cukup
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(75)
            ->width(800)
            ->height(800)
            ->fit(Fit::Max, 800, 800)
            ->sharpen(10)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('default');

        // Thumbnail untuk card listing
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(70)
            ->width(400)
            ->height(400)
            ->fit(Fit::Max, 400, 400)
            ->nonQueued()
            ->performOnCollections('default');

        // Gallery conversions (detail & lightbox thumbnails)
        // Sedikit dinaikkan quality agar tidak terlalu buram di feed
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(90)
            ->width(1400)
            ->height(900)
            ->fit(Fit::Max, 1400, 900)
            ->sharpen(10)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('gallery');

        // Gallery thumbnail (grid kecil)
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(85)
            ->width(500)
            ->height(375)
            ->fit(Fit::Max, 500, 375)
            ->nonQueued()
            ->performOnCollections('gallery');
    }
}