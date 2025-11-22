<?php

namespace Database\Seeders;

use App\Models\BioLink;
use Illuminate\Database\Seeder;

class BioLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'title' => 'Slot Iklan Event Lari',
                'subtitle' => 'Promosikan event Anda di platform #1',
                'url' => 'https://indonesiamarathon.com/rate-card',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Slot Iklan Event Lari',
                'subtitle' => 'Paket iklan premium untuk jangkauan maksimal',
                'url' => 'https://indonesiamarathon.com/rate-card',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Slot Iklan Event Lari',
                'subtitle' => 'Kolaborasi dengan komunitas pelari terbesar',
                'url' => 'https://indonesiamarathon.com/rate-card',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Kalender Event Lari',
                'subtitle' => 'Jadwal lari 2025 terlengkap di Indonesia',
                'url' => 'https://indonesiamarathon.com/event',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Website Indonesia Marathon',
                'subtitle' => 'indonesiamarathon.com',
                'url' => 'https://indonesiamarathon.com',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Info Paid Partnership',
                'subtitle' => 'Kolaborasi & kemitraan untuk brand',
                'url' => 'https://indonesiamarathon.com/rate-card',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Threads',
                'subtitle' => '@indonesiamarathon',
                'url' => 'https://www.threads.net/@indonesiamarathon',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'title' => 'TikTok',
                'subtitle' => '@indonesiamarathon',
                'url' => 'https://www.tiktok.com/@indonesiamarathon',
                'order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($links as $linkData) {
            BioLink::updateOrCreate(
                ['url' => $linkData['url'], 'title' => $linkData['title']],
                $linkData
            );
        }

        $this->command->info('Bio links seeded successfully!');
    }
}