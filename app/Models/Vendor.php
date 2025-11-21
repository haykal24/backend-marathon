<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vendor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'description',
        'website',
        'email',
        'phone',
        'city',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder.jpg');
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
    }
}
