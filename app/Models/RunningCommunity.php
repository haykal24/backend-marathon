<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RunningCommunity extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($community) {
            if (empty($community->slug)) {
                $community->slug = Str::slug($community->name);
            }
        });

        static::updating(function ($community) {
            if ($community->isDirty('name') && empty($community->slug)) {
                $community->slug = Str::slug($community->name);
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'location',
        'city',
        'instagram_handle',
        'contact_info',
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
        // OPTIMASI: Logo komunitas - ukuran kecil sudah cukup
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

        // Gallery conversions
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->width(1200)
            ->height(800)
            ->fit(Fit::Max, 1200, 800)
            ->sharpen(10)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('gallery');

        // Gallery thumbnail
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(75)
            ->width(400)
            ->height(300)
            ->fit(Fit::Max, 400, 300)
            ->nonQueued()
            ->performOnCollections('gallery');
    }
}