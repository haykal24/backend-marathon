<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => '5K',
                'slug' => '5k',
            ],
            [
                'name' => '10K',
                'slug' => '10k',
            ],
            [
                'name' => '21K',
                'slug' => '21k',
            ],
            [
                'name' => '42K',
                'slug' => '42k',
            ],
            [
                'name' => 'Kids',
                'slug' => 'kids',
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}