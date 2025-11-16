<?php

namespace Database\Seeders;

use App\Models\RateCategory;
use App\Models\RatePackage;
use App\Models\RatePlacement;
use Illuminate\Database\Seeder;

class RatePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // 1. Paket Promosi Media Sosial
            [
                'name' => 'Feed Post Instagram',
                'slug' => 'social-feed-post',
                'category_slug' => 'social',
                'placement_slot' => 'instagram_feed',
                'description' => 'Satu konten feed permanen di Instagram @indonesiamarathon dengan copywriting dan CTA yang disesuaikan.',
                'price' => 150000,
                'price_display' => 'Rp 150.000 per post',
                'duration' => 'Konten tayang selamanya',
                'audience' => 'Followers @indonesiamarathon yang loyal dan aktif mengikuti event lari',
                'deliverables' => [
                    '1x Post Feed (image/reel/carousel)',
                    'Caption & CTA disesuaikan dengan kampanye Anda',
                ],
                'channels' => ['Instagram'],
                'order_column' => 1,
            ],
            [
                'name' => 'Story Blast Instagram',
                'slug' => 'social-story-blast',
                'category_slug' => 'social',
                'placement_slot' => 'instagram_story',
                'description' => 'Satu rangkaian story 24 jam dengan fokus ajakan aksi, cocok untuk pengumuman cepat.',
                'price' => 100000,
                'price_display' => 'Rp 100.000 per blast',
                'duration' => 'Tayang 24 jam',
                'audience' => 'Pelari yang memantau update harian Instagram @indonesiamarathon',
                'deliverables' => [
                    '1x Story dengan stiker link menuju landing page',
                    'Mention brand/event serta hashtag campaign',
                ],
                'channels' => ['Instagram'],
                'order_column' => 2,
            ],
            [
                'name' => 'Paket Starter Media Sosial',
                'slug' => 'social-paket-starter',
                'category_slug' => 'social',
                'placement_slot' => 'instagram_package_starter',
                'description' => 'Paket hemat untuk memperkenalkan event atau produk baru ke audiens pelari.',
                'price' => 300000,
                'price_display' => 'Rp 300.000 per paket',
                'duration' => 'Feed permanen + story 7 hari campaign',
                'audience' => 'Pelari yang mengikuti feed dan story kami',
                'deliverables' => [
                    '1x Post Feed (permanen)',
                    '2x Story dengan stiker link/mention',
                ],
                'channels' => ['Instagram'],
                'order_column' => 3,
            ],
            [
                'name' => 'Paket Komunitas Media Sosial',
                'slug' => 'social-paket-komunitas',
                'category_slug' => 'social',
                'placement_slot' => 'instagram_package_community',
                'description' => 'Paket intensif untuk meningkatkan awareness event besar dalam waktu singkat.',
                'price' => 750000,
                'price_display' => 'Rp 750.000 per paket',
                'duration' => 'Kampanye 7-10 hari',
                'audience' => 'Puluhan ribu pelari aktif yang siap daftar event baru',
                'deliverables' => [
                    '3x Post Feed (dijadwalkan strategis)',
                    '5x Story dengan link, mention, dan countdown sticker',
                ],
                'channels' => ['Instagram'],
                'order_column' => 4,
            ],

            // 2. Paket Display Ads (Website)
            [
                'name' => 'Paket Display Homepage',
                'slug' => 'display-homepage',
                'category_slug' => 'website',
                'placement_slot' => 'website_package_homepage',
                'description' => 'Visibilitas tertinggi di seluruh homepage untuk brand awareness maksimum.',
                'price' => 5000000,
                'price_display' => 'Rp 5.000.000 / 30 hari',
                'duration' => '30 hari tayang',
                'audience' => 'Pengunjung homepage IndonesiaMarathon.com (pelari, EO, vendor)',
                'deliverables' => [
                    'Banner Utama (slot banner_main)',
                    'Sidebar 1 & Sidebar 2 (rotasi 30 hari)',
                    'Laporan impresi & klik bulanan',
                ],
                'channels' => ['Website'],
                'order_column' => 5,
            ],
            [
                'name' => 'Paket Display Event',
                'slug' => 'display-event',
                'category_slug' => 'website',
                'placement_slot' => 'website_package_event',
                'description' => 'Slot khusus pada halaman agenda dan detail event untuk audiens paling relevan.',
                'price' => 3500000,
                'price_display' => 'Rp 3.500.000 / 30 hari',
                'duration' => '30 hari tayang',
                'audience' => 'Pelari yang aktif mencari jadwal lari dan registrasi event',
                'deliverables' => [
                    'Header Halaman Event (page_header_events)',
                    'Header Detail Event (page_header_event_detail)',
                    'Laporan impresi & klik bulanan',
                ],
                'channels' => ['Website'],
                'order_column' => 6,
            ],
            [
                'name' => 'Paket Display Konten',
                'slug' => 'display-content',
                'category_slug' => 'website',
                'placement_slot' => 'website_package_content',
                'description' => 'Branding kontekstual di halaman blog dan artikel edukasi komunitas.',
                'price' => 2000000,
                'price_display' => 'Rp 2.000.000 / 30 hari',
                'duration' => '30 hari tayang',
                'audience' => 'Pelari yang mencari tips, panduan, dan inspirasi lari',
                'deliverables' => [
                    'Header Halaman Blog (page_header_blog)',
                    'Header Detail Blog (page_header_blog_detail)',
                ],
                'channels' => ['Website'],
                'order_column' => 7,
            ],
            [
                'name' => 'Paket Display Direktori',
                'slug' => 'display-directory',
                'category_slug' => 'website',
                'placement_slot' => 'website_package_directory',
                'description' => 'Target niche spesifik di seluruh halaman direktori vendor dan komunitas.',
                'price' => 1500000,
                'price_display' => 'Rp 1.500.000 / 30 hari',
                'duration' => '30 hari tayang',
                'audience' => 'EO, komunitas lari, dan vendor yang mencari partner',
                'deliverables' => [
                    'Header Ekosistem & semua sub-halaman direktori (komunitas, race management, vendor)',
                    'Termasuk header FAQ dan Rate Card',
                ],
                'channels' => ['Website'],
                'order_column' => 8,
            ],

            // 3. Paket KOMBO (Bundled Event)
            [
                'name' => 'KOMBO SPRINT',
                'slug' => 'combo-sprint',
                'category_slug' => 'combo',
                'placement_slot' => 'combo_sprint',
                'description' => 'Paket dasar untuk event baru yang butuh boost cepat.',
                'price' => 950000,
                'price_display' => 'Rp 950.000 per paket (hemat Rp 100.000)',
                'duration' => 'Listing 14 hari + kampanye sosial 7 hari',
                'audience' => 'Pelari yang mengakses homepage dan sosial media kami',
                'deliverables' => [
                    'Listing "Event Pilihan" di homepage (14 hari)',
                    'Paket Starter Media Sosial (1 feed + 2 story)',
                ],
                'channels' => ['Website', 'Instagram'],
                'order_column' => 9,
            ],
            [
                'name' => 'KOMBO MARATHON',
                'slug' => 'combo-marathon',
                'category_slug' => 'combo',
                'placement_slot' => 'combo_marathon',
                'description' => 'Paket populer untuk event besar dengan target registrasi tinggi.',
                'price' => 2400000,
                'price_display' => 'Rp 2.400.000 per paket (hemat Rp 350.000)',
                'duration' => 'Listing 14 hari + kampanye sosial dan banner 7 hari',
                'audience' => 'Pelari aktif, EO, dan komunitas lari nasional',
                'deliverables' => [
                    'Listing "Event Pilihan" di homepage (14 hari)',
                    'Paket Komunitas Media Sosial (3 feed + 5 story)',
                    'Slot banner_main (Homepage Hero) selama 7 hari',
                ],
                'channels' => ['Website', 'Instagram'],
                'order_column' => 10,
            ],
            [
                'name' => 'KOMBO ULTRA',
                'slug' => 'combo-ultra',
                'category_slug' => 'combo',
                'placement_slot' => 'combo_ultra',
                'description' => 'Solusi premium all-in-one untuk event flagship.',
                'price' => 4500000,
                'price_display' => 'Rp 4.500.000 per paket',
                'duration' => 'Kampanye terpadu 30 hari',
                'audience' => 'Pelari nasional, komunitas, dan database email pelari aktif',
                'deliverables' => [
                    'Semua fitur KOMBO MARATHON',
                    '1x Artikel Blog Review Event (tayang permanen)',
                    'Dedicated Email Blast ke database pelari',
                    'Social Media Boost (iklan tertarget)',
                ],
                'channels' => ['Website', 'Instagram', 'Email'],
                'order_column' => 11,
            ],

            // 4. Listing Direktori Mitra
            [
                'name' => 'Listing Basic Direktori',
                'slug' => 'listing-basic',
                'category_slug' => 'listing',
                'placement_slot' => 'listing_basic',
                'description' => 'Listing tahunan untuk memastikan brand Anda mudah ditemukan EO dan komunitas.',
                'price' => 500000,
                'price_display' => 'Rp 500.000 / tahun',
                'duration' => '12 bulan',
                'audience' => 'Event organizer dan komunitas yang mencari vendor resmi',
                'deliverables' => [
                    'Nama brand/vendor ditampilkan di direktori',
                    'Kategori layanan (medali, jersey, dsb)',
                    'Kontak utama (telepon/email)',
                ],
                'channels' => ['Website'],
                'order_column' => 12,
            ],
            [
                'name' => 'Featured Listing Direktori',
                'slug' => 'listing-featured',
                'category_slug' => 'listing',
                'placement_slot' => 'listing_featured',
                'description' => 'Posisi teratas dengan badge "Direkomendasikan" untuk meningkatkan kredibilitas.',
                'price' => 1500000,
                'price_display' => 'Rp 1.500.000 / tahun',
                'duration' => '12 bulan',
                'audience' => 'Event organizer premium dan brand lari nasional',
                'deliverables' => [
                    'Semua fitur Listing Basic',
                    'Logo brand + deskripsi bisnis',
                    'Link website & media sosial',
                    'Badge "Mitra Terverifikasi" & posisi teratas',
                ],
                'channels' => ['Website'],
                'order_column' => 13,
            ],
        ];

        foreach ($packages as $package) {
            $category = RateCategory::where('slug', $package['category_slug'])->first();
            $placement = RatePlacement::where('slot_key', $package['placement_slot'])->first();

            RatePackage::updateOrCreate(
                ['slug' => $package['slug']],
                [
                    'name' => $package['name'],
                    'category' => $category?->slug,
                    'placement_key' => $placement?->slot_key,
                    'rate_category_id' => $category?->id,
                    'rate_placement_id' => $placement?->id,
                    'description' => $package['description'],
                    'price' => $package['price'],
                    'price_display' => $package['price_display'],
                    'duration' => $package['duration'],
                    'audience' => $package['audience'],
                    'deliverables' => $package['deliverables'],
                    'channels' => $package['channels'],
                    'cta_label' => $package['cta_label'] ?? null,
                    'cta_url' => $package['cta_url'] ?? null,
                    'is_active' => true,
                    'order_column' => $package['order_column'],
                ]
            );
        }

        $this->command?->info('Rate packages seeded successfully.');
    }
}
