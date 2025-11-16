<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'category',
        'keyword',
        'question',
        'answer',
        'sort_order',
        'is_active',
        'views',
        'related_keyword',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'views' => 'integer',
    ];

    /**
     * Scope: Get only active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get FAQs by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Get FAQs by keyword
     */
    public function scopeByKeyword($query, string $keyword)
    {
        return $query->where('keyword', $keyword);
    }

    /**
     * Scope: Get sorted by order
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
