<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            '5K',
            '10K',
            '21K',
            '42K',
            'Fun Run',
            'Trail Run',
            'Virtual Run',
            'Marathon',
            'Half Marathon',
            'Ultra Marathon',
        ];

        foreach ($categories as $category) {
            EventCategory::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($category)],
                ['name' => $category]
            );
        }

        $this->command->info('Event categories seeded successfully!');
    }
}

