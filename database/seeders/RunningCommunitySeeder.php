<?php

namespace Database\Seeders;

use App\Models\RunningCommunity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RunningCommunitySeeder extends Seeder
{
    public function run(): void
    {
        $communities = [
            // Featured
            [
                'name' => 'IndoRunners',
                'location' => 'Nasional, tersebar di berbagai kota besar di Indonesia.',
                'instagram_handle' => 'indorunners',
                'contact_info' => 'info@indorunners.com',
                'is_featured' => true,
            ],
            [
                'name' => 'Runners aneh',
                'location' => 'Bandung, Taman Sari',
                'instagram_handle' => 'bandungrunners',
                'contact_info' => 'WhatsApp: 0813-4567-8901',
                'is_featured' => true,
            ],

            // Non-Featured
            [
                'name' => 'Surabaya Marathon Club',
                'location' => 'Surabaya, Taman Bungkul',
                'instagram_handle' => 'surabayamarathon',
                'contact_info' => 'WhatsApp: 0814-5678-9012',
                'is_featured' => false,
            ],
            [
                'name' => 'Yogyakarta Joggers',
                'location' => 'Yogyakarta, Alun-Alun Kidul',
                'instagram_handle' => 'jogjajoggers',
                'contact_info' => 'WhatsApp: 0815-6789-0123',
                'is_featured' => false,
            ],
            [
                'name' => 'Medan Runners',
                'location' => 'Medan, Lapangan Merdeka',
                'instagram_handle' => 'medanrunners',
                'contact_info' => 'WhatsApp: 0816-7890-1234',
                'is_featured' => false,
            ],
            [
                'name' => 'Bali Trail Runners',
                'location' => 'Denpasar, Kawasan Sanur & Ubud',
                'instagram_handle' => 'balitrailrunners',
                'contact_info' => 'WhatsApp: 0817-8901-2345',
                'is_featured' => false,
            ],
            [
                'name' => 'Makassar Running Crew',
                'location' => 'Makassar, Pantai Losari',
                'instagram_handle' => 'makassarrunning',
                'contact_info' => 'contact@makassarrunning.com',
                'is_featured' => false,
            ],
            [
                'name' => 'Semarang Runners',
                'location' => 'Semarang, Simpang Lima',
                'instagram_handle' => 'semarangrunners',
                'contact_info' => 'WA: 0811-2233-4455',
                'is_featured' => false,
            ],
        ];

        $placeholderPath = public_path('seeders/placeholder.jpg');

        foreach ($communities as $communityData) {
            $community = RunningCommunity::firstOrCreate(
                ['name' => $communityData['name']],
                $communityData
            );

            // Attach placeholder image if community doesn't have media yet
            if ($community->getMedia('default')->isEmpty() && File::exists($placeholderPath)) {
                // Copy to temp location for each community to preserve original
                $tempPath = storage_path('app/temp_placeholder_' . $community->id . '_' . time() . '_' . uniqid() . '.jpg');
                File::copy($placeholderPath, $tempPath);

                try {
                    $community->addMedia($tempPath)
                        ->usingName('Logo ' . $community->name)
                        ->toMediaCollection('default');
                } finally {
                    // Clean up temp file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                }
            }
        }

        $this->command->info('Running communities seeded successfully!');
    }
}

