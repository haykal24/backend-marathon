<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class BlogPost extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTags;

    protected $table = 'blog_posts';

    protected $fillable = [
        'blog_author_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'banner', // Backward compatibility dengan data existing
        'content',
        'published_at',
        'is_for_maraton_id',
        'seo_title',
        'seo_description',
        'status',
        'is_featured',
        'view_count',
        'tags', // Untuk TagsInput (akan di-handle di boot)
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_for_maraton_id' => 'boolean', // null = both, true = maraton.id, false = indonesiamarathon.com
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Relasi ke Author
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'blog_author_id');
    }

    /**
     * Relasi ke Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /**
     * Scope untuk artikel maraton.id (hanya maraton.id)
     */
    public function scopeForMaratonId($query)
    {
        return $query->where('is_for_maraton_id', true);
    }

    /**
     * Scope untuk artikel indonesiamarathon.com (hanya indonesiamarathon.com)
     */
    public function scopeForIndonesiaMarathon($query)
    {
        return $query->where('is_for_maraton_id', false);
    }

    /**
     * Scope untuk artikel yang tampil di kedua portal (null = both)
     */
    public function scopeForBothPortals($query)
    {
        return $query->whereNull('is_for_maraton_id');
    }

    /**
     * Scope untuk artikel yang tampil di maraton.id (termasuk both)
     */
    public function scopeVisibleOnMaratonId($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_for_maraton_id')
              ->orWhere('is_for_maraton_id', true);
        });
    }

    /**
     * Scope untuk artikel yang tampil di indonesiamarathon.com (termasuk both)
     */
    public function scopeVisibleOnIndonesiaMarathon($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_for_maraton_id')
              ->orWhere('is_for_maraton_id', false);
        });
    }

    /**
     * Scope untuk published
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope untuk featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get banner URL (prioritas: Spatie Media Library, fallback ke field banner)
     */
    public function getBannerUrlAttribute(): ?string
    {
        // Cek Spatie Media Library dulu
        $media = $this->getFirstMedia('banner');
        if ($media) {
            return $media->getUrl('webp') ?: $media->getUrl();
        }

        // Fallback ke field banner (backward compatibility)
        if ($this->banner) {
            // Jika sudah URL lengkap, return langsung
            if (filter_var($this->banner, FILTER_VALIDATE_URL)) {
                return $this->banner;
            }
            // Jika relative path, return dari storage
            return asset('storage/' . $this->banner);
        }

        return null;
    }

    /**
     * Get SEO title (fallback ke title)
     */
    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    /**
     * Get SEO description (fallback ke excerpt)
     */
    public function getSeoDescriptionAttribute($value): ?string
    {
        return $value ?: $this->excerpt;
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Sync tags from array of tag names
     */
    public function syncTagsFromArray(array $tagNames): void
    {
        $tags = [];
        foreach ($tagNames as $tagName) {
            if (empty($tagName) || trim($tagName) === '') {
                continue;
            }
            $tagName = trim($tagName);
            $tagSlug = \Illuminate\Support\Str::slug($tagName);
            
            $tag = \Spatie\Tags\Tag::firstOrCreate(
                ['slug' => $tagSlug, 'type' => 'blog'],
                ['name' => $tagName]
            );
            
            if (empty($tag->name) || empty($tag->slug)) {
                $tag->name = $tagName;
                $tag->slug = $tagSlug;
                $tag->save();
            }
            
            $tags[] = $tag;
        }
        
        $this->syncTags($tags);
    }

    /**
     * Spatie Media Library: Register collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'r2'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Spatie Media Library: Register conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // WebP conversion untuk banner (16:9 untuk Google Article)
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->width(1920)
            ->height(1080)
            ->fit(Fit::Max, 1920, 1080)
            ->sharpen(10)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('banner');

        // Thumbnail untuk listing (4:3)
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->quality(75)
            ->width(800)
            ->height(600)
            ->fit(Fit::Max, 800, 600)
            ->nonQueued()
            ->performOnCollections('banner');

        // Square untuk featured (1:1)
        $this->addMediaConversion('square')
            ->format('webp')
            ->quality(75)
            ->width(1200)
            ->height(1200)
            ->fit(Fit::Max, 1200, 1200)
            ->nonQueued()
            ->performOnCollections('banner');
    }
}

