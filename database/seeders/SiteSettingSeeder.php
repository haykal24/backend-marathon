<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = now()->year;

        $customDefaultValues = [
            'site_name' => 'indonesiamarathon.com',
            'site_description' => 'Platform digital #1 di Indonesia sebagai pusat informasi dan komunitas event lari. Temukan jadwal lari terbaru, direktori vendor, dan artikel seputar dunia lari.',
            'contact_email' => 'kontak@indonesiamarathon.com',
            'contact_phone' => '+62 21 1234 5678',
            'contact_whatsapp' => '+62 812 3456 7890',
            'address' => "Indonesia Marathon HQ\nJl. Atletik No. 88\nJakarta Selatan 12190\nDKI Jakarta, Indonesia",
            'instagram_handle' => '@indonesiamarathon',
            'facebook_url' => 'https://www.facebook.com/indonesiamarathon',
            'twitter_handle' => '@indomarathon',
            'tiktok_handle' => '@indonesiamarathon',

            // Header backgrounds akan di-seed dengan placeholder default
            // Tidak perlu mendefinisikan nilainya di sini kecuali ingin custom
            // 'header_bg_events' => null,
            // 'header_bg_blog' => null,

            'footer_description' => 'indonesiamarathon.com adalah platform digital #1 untuk kalender lari, direktori vendor, dan komunitas pelari di seluruh Indonesia.',
            'footer_copyright' => fn () => sprintf(
                '© %s indonesiamarathon.com. All rights reserved.',
                $currentYear
            ),
        ];

        foreach (SiteSetting::KEY_DEFINITIONS as $key => $definition) {
            $type = $definition['type'] ?? 'text';
            $value = $customDefaultValues[$key] ?? ($definition['default'] ?? null);

            if (is_callable($value)) {
                $value = $value();
            }

            $group = $definition['group'] ?? 'general';
            $description = $definition['description'] ?? null;

            $siteSetting = SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $type === 'image'
                        ? null
                        : (is_array($value) ? json_encode($value) : $value),
                    'type' => $type,
                    'group' => $group,
                    'description' => $description,
                ]
            );

            if ($type === 'image') {
                $seedFile = $definition['seed'] ?? null;
                $imagePath = $seedFile
                    ? public_path('seeders/' . $seedFile)
                    : public_path('seeders/placeholder.jpg');

                if (!File::exists($imagePath)) {
                    $imagePath = public_path('seeders/placeholder.jpg');
                }

                if ($siteSetting->getMedia('default')->isEmpty() && File::exists($imagePath)) {
                    $tempPath = storage_path('app/temp_' . $key . '_' . time() . '_' . uniqid() . '.' . pathinfo($imagePath, PATHINFO_EXTENSION));
                    File::copy($imagePath, $tempPath);

                    try {
                        $siteSetting->addMedia($tempPath)
                            ->usingName($key)
                            ->toMediaCollection('default');
                    } finally {
                        if (File::exists($tempPath)) {
                            File::delete($tempPath);
                        }
                    }
                }
            }
        }
    }
}