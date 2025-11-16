<?php

namespace Database\Seeders;

use App\Models\RatePlacement;
use Illuminate\Database\Seeder;

class RatePlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            // Website display slots
            [
                'name' => 'Homepage - Hero Slider',
                'slug' => 'homepage-slider',
                'slot_key' => 'homepage_slider',
                'description' => 'Banner utama full-width di hero homepage.',
                'order_column' => 1,
            ],
            [
                'name' => 'Homepage - Banner Utama',
                'slug' => 'banner-main',
                'slot_key' => 'banner_main',
                'description' => 'Banner utama di area konten homepage.',
                'order_column' => 2,
            ],
            [
                'name' => 'Homepage - Sidebar 1',
                'slug' => 'sidebar-1',
                'slot_key' => 'sidebar_1',
                'description' => 'Banner sidebar pertama di homepage.',
                'order_column' => 3,
            ],
            [
                'name' => 'Homepage - Sidebar 2',
                'slug' => 'sidebar-2',
                'slot_key' => 'sidebar_2',
                'description' => 'Banner sidebar kedua di homepage.',
                'order_column' => 4,
            ],
            [
                'name' => 'Header Halaman Event',
                'slug' => 'page-header-events',
                'slot_key' => 'page_header_events',
                'description' => 'Slider banner di header halaman daftar event.',
                'order_column' => 5,
            ],
            [
                'name' => 'Header Detail Event',
                'slug' => 'page-header-event-detail',
                'slot_key' => 'page_header_event_detail',
                'description' => 'Banner di header halaman detail event.',
                'order_column' => 6,
            ],
            [
                'name' => 'Header Halaman Blog',
                'slug' => 'page-header-blog',
                'slot_key' => 'page_header_blog',
                'description' => 'Banner promosi di header halaman blog.',
                'order_column' => 7,
            ],
            [
                'name' => 'Header Detail Blog',
                'slug' => 'page-header-blog-detail',
                'slot_key' => 'page_header_blog_detail',
                'description' => 'Banner promosi di header artikel blog.',
                'order_column' => 8,
            ],
            [
                'name' => 'Header FAQ',
                'slug' => 'page-header-faq',
                'slot_key' => 'page_header_faq',
                'description' => 'Banner promosi di header halaman FAQ.',
                'order_column' => 9,
            ],
            [
                'name' => 'Header Ekosistem',
                'slug' => 'page-header-ekosistem',
                'slot_key' => 'page_header_ekosistem',
                'description' => 'Banner di halaman induk ekosistem.',
                'order_column' => 10,
            ],
            [
                'name' => 'Header Komunitas Lari',
                'slug' => 'page-header-komunitas',
                'slot_key' => 'page_header_komunitas',
                'description' => 'Banner di direktori komunitas lari.',
                'order_column' => 11,
            ],
            [
                'name' => 'Header Race Management',
                'slug' => 'page-header-race-management',
                'slot_key' => 'page_header_race_management',
                'description' => 'Banner di direktori race management.',
                'order_column' => 12,
            ],
            [
                'name' => 'Header Vendor Medali',
                'slug' => 'page-header-vendor-medali',
                'slot_key' => 'page_header_vendor_medali',
                'description' => 'Banner di direktori vendor medali.',
                'order_column' => 13,
            ],
            [
                'name' => 'Header Vendor Jersey',
                'slug' => 'page-header-vendor-jersey',
                'slot_key' => 'page_header_vendor_jersey',
                'description' => 'Banner di direktori vendor jersey.',
                'order_column' => 14,
            ],
            [
                'name' => 'Header Vendor Fotografer',
                'slug' => 'page-header-vendor-fotografer',
                'slot_key' => 'page_header_vendor_fotografer',
                'description' => 'Banner di direktori vendor fotografer.',
                'order_column' => 15,
            ],
            [
                'name' => 'Header Rate Card',
                'slug' => 'page-header-rate-card',
                'slot_key' => 'page_header_rate_card',
                'description' => 'Banner di halaman rate card.',
                'order_column' => 16,
            ],

            // Social & bundle placements
            [
                'name' => 'Instagram Feed Post',
                'slug' => 'instagram-feed',
                'slot_key' => 'instagram_feed',
                'description' => 'Konten permanen di feed Instagram @indonesiamarathon.',
                'order_column' => 21,
            ],
            [
                'name' => 'Instagram Story Blast',
                'slug' => 'instagram-story',
                'slot_key' => 'instagram_story',
                'description' => 'Story dengan stiker link (tayang 24 jam).',
                'order_column' => 22,
            ],
            [
                'name' => 'Instagram Paket Starter',
                'slug' => 'instagram-package-starter',
                'slot_key' => 'instagram_package_starter',
                'description' => 'Bundle promosi starter (1 feed + 2 story).',
                'order_column' => 23,
            ],
            [
                'name' => 'Instagram Paket Komunitas',
                'slug' => 'instagram-package-community',
                'slot_key' => 'instagram_package_community',
                'description' => 'Bundle promosi komunitas (3 feed + 5 story).',
                'order_column' => 24,
            ],
            [
                'name' => 'Newsletter Blast',
                'slug' => 'newsletter-blast',
                'slot_key' => 'newsletter_blast',
                'description' => 'Highlight di newsletter komunitas pelari.',
                'order_column' => 25,
            ],
            [
                'name' => 'Community Activation',
                'slug' => 'community-activation',
                'slot_key' => 'community_activation',
                'description' => 'Kolaborasi aktivasi offline bersama komunitas pelari.',
                'order_column' => 26,
            ],

            // Combo & listing placements
            [
                'name' => 'Paket Kombinasi Sprint',
                'slug' => 'combo-sprint',
                'slot_key' => 'combo_sprint',
                'description' => 'Bundle listing event pilihan + paket starter sosmed.',
                'order_column' => 31,
            ],
            [
                'name' => 'Paket Kombinasi Marathon',
                'slug' => 'combo-marathon',
                'slot_key' => 'combo_marathon',
                'description' => 'Bundle populer dengan hero banner + paket komunitas sosmed.',
                'order_column' => 32,
            ],
            [
                'name' => 'Paket Kombinasi Ultra',
                'slug' => 'combo-ultra',
                'slot_key' => 'combo_ultra',
                'description' => 'Bundle premium dengan artikel blog & email blast.',
                'order_column' => 33,
            ],
            [
                'name' => 'Listing Direktori Basic',
                'slug' => 'listing-basic',
                'slot_key' => 'listing_basic',
                'description' => 'Listing tahunan dengan informasi dasar brand/vendor.',
                'order_column' => 41,
            ],
            [
                'name' => 'Listing Direktori Featured',
                'slug' => 'listing-featured',
                'slot_key' => 'listing_featured',
                'description' => 'Listing premium dengan logo, deskripsi lengkap, dan badge verifikasi.',
                'order_column' => 42,
            ],
        ];

        foreach ($placements as $placement) {
            RatePlacement::updateOrCreate(
                ['slot_key' => $placement['slot_key']],
                $placement
            );
        }

        $this->command?->info('Rate placements seeded successfully.');
    }
}
