<?php

namespace Database\Seeders;

use App\Models\RateCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Paket Promosi Media Sosial',
                'slug' => 'social',
                'description' => 'Jangkau audiens @indonesiamarathon melalui konten feed dan story.',
                'order_column' => 1,
                'default_group' => 'social',
            ],
            [
                'name' => 'Paket Display Ads (Website)',
                'slug' => 'website',
                'description' => 'Ambil slot strategis di homepage, halaman event, blog, dan direktori.',
                'order_column' => 2,
                'default_group' => 'website',
            ],
            [
                'name' => 'Paket KOMBO (Bundled Event)',
                'slug' => 'combo',
                'description' => 'Solusi all-in-one yang menggabungkan website, sosial media, dan kampanye tambahan.',
                'order_column' => 3,
                'default_group' => 'combo',
            ],
            [
                'name' => 'Listing Direktori Mitra',
                'slug' => 'listing',
                'description' => 'Pastikan brand/vendor Anda mudah ditemukan EO dan komunitas lari sepanjang tahun.',
                'order_column' => 4,
                'default_group' => 'listing',
            ],
        ];

        foreach ($categories as $category) {
            RateCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command?->info('Rate categories seeded successfully.');
    }
}
