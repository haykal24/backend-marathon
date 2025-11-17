<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventTypes = [
            [
                'name' => 'Fun Run',
                'slug' => 'fun_run',
                'description' => 'Event lari santai untuk semua kalangan',
                'is_active' => true,
            ],
            [
                'name' => '5K',
                'slug' => '5k',
                'description' => 'Lari jarak 5 kilometer',
                'is_active' => true,
            ],
            [
                'name' => '10K',
                'slug' => '10k',
                'description' => 'Lari jarak 10 kilometer',
                'is_active' => true,
            ],
            [
                'name' => 'Half Marathon',
                'slug' => 'half_marathon',
                'description' => 'Lari jarak 21 kilometer',
                'is_active' => true,
            ],
            [
                'name' => 'Marathon',
                'slug' => 'marathon',
                'description' => 'Lari jarak 42 kilometer',
                'is_active' => true,
            ],
            [
                'name' => 'Trail Run',
                'slug' => 'trail_run',
                'description' => 'Lari di jalur trail dengan medan berbukit',
                'is_active' => true,
            ],
            [
                'name' => 'Ultra Marathon',
                'slug' => 'ultra_marathon',
                'description' => 'Lari jarak lebih dari 42 kilometer',
                'is_active' => true,
            ],
        ];

        foreach ($eventTypes as $type) {
            EventType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}