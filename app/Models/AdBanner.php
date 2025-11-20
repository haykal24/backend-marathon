<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AdBanner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const NON_HEADER_SLOT_OPTIONS = [
        'homepage_slider' => 'Homepage - Hero Slider',
        'banner_main' => 'Homepage - Banner Utama',
        'sidebar_1' => 'Homepage - Sidebar 1',
        'sidebar_2' => 'Homepage - Sidebar 2',
    ];

    public const PAGE_HEADER_SLOT_LABELS = [
        'page_header_events' => 'Header - Halaman Event',
        'page_header_event_detail' => 'Header - Detail Event',
        'page_header_blog' => 'Header - Halaman Blog',
        'page_header_blog_detail' => 'Header - Detail Blog',
        'page_header_faq' => 'Header - Halaman FAQ',
        'page_header_ekosistem' => 'Header - Ekosistem',
        'page_header_komunitas' => 'Header - Direktori Komunitas',
        'page_header_race_management' => 'Header - Direktori Race Management',
        'page_header_vendor_medali' => 'Header - Direktori Vendor Medali',
        'page_header_vendor_jersey' => 'Header - Direktori Vendor Jersey',
        'page_header_vendor_fotografer' => 'Header - Direktori Fotografer',
        'page_header_rate_card' => 'Header - Halaman Rate Card',
    ];

    protected $fillable = [
        'name',
        'target_url',
        'slot_location',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public static function slotOptions(): array
    {
        $options = [];

        foreach (self::NON_HEADER_SLOT_OPTIONS as $slot => $label) {
            $options[$slot] = "{$label} (Desktop)";

            if ($slot !== 'homepage_slider') {
                $options["{$slot}_mobile"] = "{$label} (Mobile)";
            }
        }

        foreach (self::PAGE_HEADER_SLOT_LABELS as $slot => $label) {
            $options[$slot] = "{$label} (Desktop)";
            $options["{$slot}_mobile"] = "{$label} (Mobile)";
        }

        return $options;
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
        // OPTIMASI: Banner besar (slider, hero) - quality tinggi tapi bukan full HD
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(75) // Lighthouse merekomendasikan quality 70-75
            ->width(1920)
            ->height(1080)
            ->fit('max', 1920, 1080)
            ->sharpen(10)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('default');

        // Mobile banner - lebih kecil dan agresif kompresi
        $this->addMediaConversion('mobile')
            ->format('webp')
            ->quality(70)
            ->width(800)
            ->height(600)
            ->fit('max', 800, 600)
            ->nonQueued()
            ->performOnCollections('default');
    }
}