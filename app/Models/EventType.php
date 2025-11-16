<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class EventType extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($eventType) {
            if (empty($eventType->slug)) {
                $eventType->slug = Str::slug($eventType->name);
            }
        });
    }

    /**
     * Relasi ke Events berdasarkan event_type
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_type', 'slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk('public')
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

    public function getImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('default');
        return $media ? $media->getUrl('webp') : null;
    }

    /**
     * Hitung jumlah event (semua status)
     */
    public function getEventCountAttribute(): int
    {
        return $this->events()->count();
    }
}
