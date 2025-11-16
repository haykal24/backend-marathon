<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PillarEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Pillar Events...');

        $pillarEvents = [
            [
                'title' => 'Borobudur Marathon 2025',
                'slug' => 'borobudur-marathon-2025',
                'description' => 'Borobudur Marathon adalah salah satu event marathon paling bergengsi di Indonesia. Nikmati pengalaman lari dengan latar belakang Candi Borobudur yang megah. Event ini menawarkan kategori Full Marathon, Half Marathon, dan 10K.',
                'location_name' => 'Taman Lumbini, Candi Borobudur',
                'city' => 'Magelang',
                'event_date' => '2025-11-16',
                'event_type' => 'road_run',
                'status' => 'published',
                'registration_url' => 'https://borobudurmarathon.com',
                'organizer_name' => 'Yayasan Borobudur Marathon',
                'benefits' => json_encode(['Race Pack', 'Medali Finisher', 'Refreshment']),
                'registration_fees' => json_encode(['Marathon' => 'IDR 750.000', 'Half Marathon' => 'IDR 550.000']),
                'social_media' => json_encode(['Instagram' => '@borobudur.marathon']),
                'categories' => ['Marathon', 'Half Marathon', '10K'],
            ],
            [
                'title' => 'Jakarta Marathon 2025',
                'slug' => 'jakarta-marathon-2025',
                'description' => 'Jakarta Marathon adalah event lari tahunan terbesar di ibu kota. Rasakan sensasi lari melintasi ikon-ikon kota Jakarta. Kategori yang tersedia mencakup Full Marathon, Half Marathon, 10K, 5K, dan Maratoonz (untuk anak-anak).',
                'location_name' => 'Gelora Bung Karno (GBK)',
                'city' => 'Jakarta',
                'event_date' => '2025-10-26',
                'event_type' => 'road_run',
                'status' => 'published',
                'registration_url' => 'https://thejakartamarathon.com',
                'organizer_name' => 'Inspiro',
                'benefits' => json_encode(['Jersey Eksklusif', 'Medali Finisher', 'Race Bag']),
                'registration_fees' => json_encode(['Marathon' => 'IDR 800.000', 'Half Marathon' => 'IDR 600.000', '10K' => 'IDR 400.000']),
                'social_media' => json_encode(['Instagram' => '@jakartamarathon']),
                'categories' => ['Marathon', 'Half Marathon', '10K', '5K'],
            ]
        ];

        foreach ($pillarEvents as $eventData) {
            $categories = $eventData['categories'];
            unset($eventData['categories']);

            $event = Event::updateOrCreate(
                ['slug' => $eventData['slug']],
                $eventData
            );

            $categoryIds = [];
            foreach ($categories as $categoryName) {
                $category = EventCategory::firstOrCreate([
                    'slug' => Str::slug($categoryName),
                ], [
                    'name' => $categoryName,
                ]);
                $categoryIds[] = $category->id;
            }
            $event->categories()->sync($categoryIds);

            // Placeholder untuk Spatie Media Library
            // Di produksi, gambar akan di-attach melalui command import atau Filament.
            // Untuk seeder, kita bisa skip atau attach placeholder jika ada.
            if (! $event->hasMedia()) {
                 // $event->addMedia(public_path('seeders/placeholder-event.jpg'))->preservingOriginal()->toMediaCollection();
            }
        }
    }
}