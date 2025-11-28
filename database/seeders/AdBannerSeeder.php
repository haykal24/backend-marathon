<?php

namespace Database\Seeders;

use App\Models\AdBanner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdBannerSeeder extends Seeder
{
    public function run(): void
    {
        $bannerDefinitions = [
            [
                'name' => 'Hero Slider Partner A',
                'target_url' => 'https://example.com/promo-hero-a',
                'slot_location' => 'homepage_slider',
            ],
            [
                'name' => 'Hero Slider Partner B',
                'target_url' => 'https://example.com/promo-hero-b',
                'slot_location' => 'homepage_slider',
            ],
            [
                'name' => 'Homepage Banner Utama',
                'target_url' => 'https://example.com/banner-main',
                'slot_location' => 'banner_main',
            ],
            [
                'name' => 'Homepage Sidebar Banner 1',
                'target_url' => 'https://example.com/sidebar-1',
                'slot_location' => 'sidebar_1',
            ],
            [
                'name' => 'Homepage Sidebar Banner 2',
                'target_url' => 'https://example.com/sidebar-2',
                'slot_location' => 'sidebar_2',
            ],
            [
                'name' => 'Header Agenda Lari - Sponsor EO',
                'target_url' => 'https://example.com/header-events',
                'slot_location' => 'page_header_events',
            ],
            [
                'name' => 'Header Detail Event - Ticketing Partner',
                'target_url' => 'https://example.com/header-event-detail',
                'slot_location' => 'page_header_event_detail',
            ],
            [
                'name' => 'Header Blog - Konten Sponsor',
                'target_url' => 'https://example.com/header-blog',
                'slot_location' => 'page_header_blog',
            ],
            [
                'name' => 'Header Artikel - Tips Pelari',
                'target_url' => 'https://example.com/header-blog-detail',
                'slot_location' => 'page_header_blog_detail',
            ],
            [
                'name' => 'Header FAQ - Support Center',
                'target_url' => 'https://example.com/header-faq',
                'slot_location' => 'page_header_faq',
            ],
            [
                'name' => 'Header Ekosistem - Kolaborasi Partner',
                'target_url' => 'https://example.com/header-ekosistem',
                'slot_location' => 'page_header_ekosistem',
            ],
            [
                'name' => 'Header Komunitas Lari - Sponsor Running Club',
                'target_url' => 'https://example.com/header-komunitas',
                'slot_location' => 'page_header_komunitas',
            ],
            [
                'name' => 'Header Race Management - Jasa EO',
                'target_url' => 'https://example.com/header-race-management',
                'slot_location' => 'page_header_race_management',
            ],
            [
                'name' => 'Header Vendor Medali - Produsen Lokal',
                'target_url' => 'https://example.com/header-vendor-medali',
                'slot_location' => 'page_header_vendor_medali',
            ],
            [
                'name' => 'Header Vendor Jersey - Apparel Partner',
                'target_url' => 'https://example.com/header-vendor-jersey',
                'slot_location' => 'page_header_vendor_jersey',
            ],
            [
                'name' => 'Header Vendor Fotografer - Race Photography',
                'target_url' => 'https://example.com/header-vendor-fotografer',
                'slot_location' => 'page_header_vendor_fotografer',
            ],
            [
                'name' => 'Header Rate Card - Advertise With Us',
                'target_url' => 'https://example.com/header-rate-card',
                'slot_location' => 'page_header_rate_card',
            ],
        ];

        $placeholderPath = public_path('seeders/placeholder.jpg');

        foreach ($bannerDefinitions as $definition) {
            $this->createBanner($definition, $placeholderPath);

            if ($this->shouldGenerateMobileVariant($definition['slot_location'])) {
                $mobileDefinition = [
                    'name' => $definition['name'] . ' (Mobile)',
                    'target_url' => $definition['target_url'],
                    'slot_location' => $definition['slot_location'] . '_mobile',
                ];

                $this->createBanner($mobileDefinition, $placeholderPath);
            }
        }

        $this->command->info('Ad banners seeded successfully!');
    }

    protected function createBanner(array $definition, string $placeholderPath): void
    {
        $banner = AdBanner::firstOrCreate(
            ['name' => $definition['name']],
            [
                'target_url' => $definition['target_url'],
                'slot_location' => $definition['slot_location'],
                'is_active' => true,
                'expires_at' => now()->addMonths(3),
            ]
        );

        if ($banner->getMedia('default')->isEmpty() && File::exists($placeholderPath)) {
            $tempPath = storage_path('app/temp_placeholder_' . $banner->id . '_' . uniqid() . '.jpg');
            File::copy($placeholderPath, $tempPath);

            try {
                $banner->addMedia($tempPath)
                    ->usingName($banner->name)
                    ->toMediaCollection('default', config('media-library.disk_name', 'r2'));
            } finally {
                if (File::exists($tempPath)) {
                    File::delete($tempPath);
                }
            }
        }
    }

    protected function shouldGenerateMobileVariant(string $slot): bool
    {
        $nonHeaderSlotsWithMobile = [
            'banner_main',
            'sidebar_1',
            'sidebar_2',
        ];

        return Str::startsWith($slot, 'page_header_') || in_array($slot, $nonHeaderSlotsWithMobile, true);
    }
}