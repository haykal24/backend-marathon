<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BioLink extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'subtitle',
        'url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();

        $this->addMediaConversion('webp')
            ->format('webp')
            ->fit(Fit::Crop, 300, 300)
            ->nonQueued();
    }
}