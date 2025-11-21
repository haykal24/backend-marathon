<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'rate_package_id',
        'title',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'location_name',
        'city',
        'province',
        'event_date',
        'event_end_date',
        'event_type',
        'registration_url',
        'organizer_name',
        'benefits',
        'contact_info',
        'registration_fees',
        'social_media',
        'status',
        'is_featured_hero',
        'featured_hero_expires_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_end_date' => 'date',
        'benefits' => 'array',
        'contact_info' => 'array',
        'registration_fees' => 'array',
        'social_media' => 'array',
        'is_featured_hero' => 'boolean',
        'featured_hero_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ratePackage(): BelongsTo
    {
        return $this->belongsTo(RatePackage::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(EventCategory::class, 'category_event', 'event_id', 'category_id');
    }

    /**
     * Relasi ke Province berdasarkan province name
     */
    public function provinceRelation(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province', 'name');
    }

    /**
     * Relasi ke EventType berdasarkan event_type slug
     */
    public function eventTypeRelation(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type', 'slug');
    }

    protected static function booted(): void
    {
        static::saved(function (self $event): void {
            if ($event->wasChanged([
                'is_featured_hero',
                'featured_hero_expires_at',
                'status',
                'event_date',
                'created_at',
            ])) {
                Cache::forget('featured_hero_events');
            }
        });

        static::deleted(function (): void {
            Cache::forget('featured_hero_events');
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk('public');
        // Removed useFallbackUrl - biarkan null jika tidak ada image
        // Frontend akan handle fallback dengan bg-primary gradient
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // OPTIMASI: Hero/Detail view - high quality untuk poster utama
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(75) // Turunkan dari 80 ke 75 (sweet spot)
            ->width(1920)
            ->height(1080)
            ->fit(Fit::Max, 1920, 1080) // Fit tanpa crop (menggunakan enum Spatie\Image\Enums\Fit)
            ->sharpen(10)
            ->withResponsiveImages() // Generate srcset untuk berbagai ukuran
            ->nonQueued()
            ->performOnCollections('default');

        // OPTIMASI: Card thumbnail - ukuran kecil untuk listing
        // Lighthouse: "image file is larger than it needs to be"
        $this->addMediaConversion('card_thumb')
            ->format('webp')
            ->quality(70) // Lebih agresif untuk thumbnail
            ->width(800) // Resize ke 800px (cukup untuk card view)
            ->height(500)
            ->fit(Fit::Max, 800, 500)
            ->sharpen(5)
            ->nonQueued()
            ->performOnCollections('default');

        // OPTIMASI: Mobile thumbnail - ultra compact
        $this->addMediaConversion('mobile_thumb')
            ->format('webp')
            ->quality(65)
            ->width(600)
            ->height(400)
            ->fit(Fit::Max, 600, 400)
            ->nonQueued()
            ->performOnCollections('default');
    }
    
    public function getMediaPath(): string
    {
        return 'events';
    }
    
    public function getWebpImageUrl(): ?string
    {
        $media = $this->getFirstMedia('default');
        if (!$media) {
            return null;
        }
        
        if ($media->hasGeneratedConversion('webp')) {
            return $media->getUrl('webp');
        }
        
        return $media->getUrl();
    }
}