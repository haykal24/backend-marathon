<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class Province extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_featured_frontend',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured_frontend' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($province) {
            if (empty($province->slug)) {
                $province->slug = Str::slug($province->name);
            }
        });
    }

    /**
     * Relasi ke Events berdasarkan province name
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'province', 'name');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'))
            ->useFallbackUrl('/images/placeholder.jpg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->width(800)
            ->sharpen(10)
            ->nonQueued()
            ->performOnCollections('default');
    }

    public function getThumbnailAttribute(): ?string
    {
        $media = $this->getFirstMedia('default');
        return $media ? $media->getUrl('webp') : null;
    }

    /**
     * Hitung jumlah event (semua status)
     */
    public function getEventCountAttribute(): int
    {
        if (array_key_exists('events_count', $this->attributes)) {
            return (int) $this->attributes['events_count'];
        }

        return $this->events()->count();
    }
}