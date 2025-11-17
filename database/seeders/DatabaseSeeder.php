<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\StaticPageSeeder;
use Database\Seeders\VendorSeeder;
use Database\Seeders\PillarEventSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            EventTypeSeeder::class,
            EventCategorySeeder::class,
            SiteSettingSeeder::class, // Site settings (logo, kontak, dll)
            VendorSeeder::class,
            RunningCommunitySeeder::class,
            AdBannerSeeder::class,
            RateCategorySeeder::class,
            RatePlacementSeeder::class,
            StaticPageSeeder::class,
            BlogSeeder::class,
            PillarEventSeeder::class,
            FAQSeeder::class,
            RatePackageSeeder::class,
        ]);
    }
}