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

    public function getImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('default');
        return $media ? $media->getUrl('webp') : null;
    }

    /**
     * Hitung jumlah event (semua status)
     * 
     * Note: Gunakan withCount('events') di query untuk menghindari N+1
     * Accessor ini fallback jika withCount tidak di-load
     */
    public function getEventCountAttribute(): int
    {
        // Jika withCount sudah di-load, gunakan itu (menghindari N+1)
        if (array_key_exists('events_count', $this->attributes)) {
            return (int) $this->attributes['events_count'];
        }
        
        // Fallback ke query (hanya jika withCount tidak ada)
        return $this->events()->count();
    }
}
