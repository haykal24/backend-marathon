<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogAuthor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'blog_authors';

    protected $fillable = [
        'name',
        'email',
        'photo', // Backward compatibility
        'bio',
        'github_handle',
        'twitter_handle',
    ];

    /**
     * Relasi ke Posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'blog_author_id');
    }

    /**
     * Get photo URL (prioritas: Spatie Media Library, fallback ke field photo)
     */
    public function getPhotoUrlAttribute(): ?string
    {
        // Cek Spatie Media Library dulu
        $media = $this->getFirstMedia('photo');
        if ($media) {
            return $media->getUrl('thumb') ?: $media->getUrl();
        }

        // Fallback ke field photo (backward compatibility)
        if ($this->photo) {
            if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
                return $this->photo;
            }
            return asset('storage/' . $this->photo);
        }

        return null;
    }

    /**
     * Spatie Media Library: Register collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Spatie Media Library: Register conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Thumbnail untuk author photo
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(80)
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop, 300, 300)
            ->nonQueued()
            ->performOnCollections('photo');
    }
}

