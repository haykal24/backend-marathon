<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasFactory;

    protected $table = 'blog_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * Relasi ke Posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'blog_category_id');
    }

    /**
     * Scope untuk visible categories
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Get SEO title (fallback ke name)
     */
    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->name;
    }

    /**
     * Get SEO description (fallback ke description)
     */
    public function getSeoDescriptionAttribute($value): ?string
    {
        return $value ?: $this->description;
    }
}

