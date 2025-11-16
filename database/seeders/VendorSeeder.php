<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            // Medali
            [
                'name' => 'Medali Runner Indonesia',
                'type' => 'medali',
                'description' => 'Spesialis pembuatan medali custom untuk event lari dengan kualitas premium dan desain inovatif. Melayani seluruh Indonesia.',
                'city' => 'Jakarta', 'website' => 'https://medalirunner.com', 'email' => 'info@medalirunner.com', 'phone' => '0812-3456-7890', 'is_featured' => true,
            ],
            [
                'name' => 'Juara Medali',
                'type' => 'medali',
                'description' => 'Produsen medali lari dengan harga kompetitif dan pilihan material beragam. Cocok untuk event skala kecil hingga besar.',
                'city' => 'Bandung', 'website' => 'https://juaramedali.com', 'email' => 'sales@juaramedali.com', 'phone' => '0813-1111-2222', 'is_featured' => false,
            ],
            [
                'name' => 'Nusantara Awards',
                'type' => 'medali',
                'description' => 'Menyediakan medali, piala, dan plakat untuk berbagai event olahraga, termasuk lari. Berpengalaman sejak 2010.',
                'city' => 'Surabaya', 'website' => 'https://nusantaraawards.com', 'email' => 'contact@nusantaraawards.com', 'phone' => '0814-2222-3333', 'is_featured' => false,
            ],

            // Race Management
            [
                'name' => 'Race Management Pro',
                'type' => 'race_management',
                'description' => 'Layanan manajemen event lari profesional dengan tim berpengalaman, sistem timing akurat, dan teknologi terdepan.',
                'city' => 'Jakarta', 'website' => 'https://racemgmtpro.com', 'email' => 'contact@racemgmtpro.com', 'phone' => '0813-4567-8901', 'is_featured' => true,
            ],
            [
                'name' => 'Event Runner Solutions',
                'type' => 'race_management',
                'description' => 'Solusi lengkap untuk manajemen event lari dari perencanaan, perizinan, hingga eksekusi hari H. Terbukti menangani event nasional.',
                'city' => 'Tangerang', 'website' => 'https://eventrunnersolutions.com', 'email' => 'info@eventrunnersolutions.com', 'phone' => '0817-8901-2345', 'is_featured' => false,
            ],
            [
                'name' => 'Lintas Sumbang',
                'type' => 'race_management',
                'description' => 'Race management yang berbasis di Jawa Tengah, berpengalaman dalam mengelola event lari trail dan road run di berbagai kota.',
                'city' => 'Semarang', 'website' => 'https://lintassumbang.id', 'email' => 'halo@lintassumbang.id', 'phone' => '0818-9012-3456', 'is_featured' => false,
            ],

            // Jersey
            [
                'name' => 'Jersey Custom Run',
                'type' => 'jersey',
                'description' => 'Produksi jersey lari custom dengan berbagai pilihan bahan berkualitas (dry-fit, microfiber) dan desain kreatif.',
                'city' => 'Yogyakarta', 'website' => 'https://jerseycustomrun.com', 'email' => 'sales@jerseycustomrun.com', 'phone' => '0814-5678-9012', 'is_featured' => false,
            ],
            [
                'name' => 'Sportswear ID',
                'type' => 'jersey',
                'description' => 'Vendor jersey olahraga untuk komunitas dan event. Menyediakan layanan desain gratis dan pengiriman cepat.',
                'city' => 'Bekasi', 'website' => 'https://sportswear.id', 'email' => 'order@sportswear.id', 'phone' => '0819-0123-4567', 'is_featured' => true,
            ],
            
            // Fotografer
            [
                'name' => 'Sport Photography Indonesia',
                'type' => 'fotografer',
                'description' => 'Jasa fotografi profesional untuk event lari dengan hasil foto berkualitas tinggi dan sistem galeri online yang mudah diakses.',
                'city' => 'Jakarta', 'website' => 'https://sportphoto.id', 'email' => 'booking@sportphoto.id', 'phone' => '0815-6789-0123', 'is_featured' => false,
            ],
            [
                'name' => 'Lensa Pelari',
                'type' => 'fotografer',
                'description' => 'Tim fotografer yang berdedikasi untuk mengabadikan momen-momen terbaik para pelari di garis finis dan sepanjang rute.',
                'city' => 'Denpasar', 'website' => 'https://lensapelari.com', 'email' => 'info@lensapelari.com', 'phone' => '0816-7890-1234', 'is_featured' => false,
            ],
        ];

        $placeholderPath = public_path('seeders/placeholder.jpg');

        foreach ($vendors as $vendorData) {
            $vendor = Vendor::firstOrCreate(
                ['name' => $vendorData['name']],
                $vendorData
            );

            // Attach placeholder image if vendor doesn't have media yet
            if ($vendor->getMedia('default')->isEmpty() && File::exists($placeholderPath)) {
                // Copy to temp location for each vendor to preserve original
                $tempPath = storage_path('app/temp_placeholder_' . $vendor->id . '_' . time() . '_' . uniqid() . '.jpg');
                File::copy($placeholderPath, $tempPath);

                try {
                    $vendor->addMedia($tempPath)
                        ->usingName('Logo ' . $vendor->name)
                        ->toMediaCollection('default');
                } finally {
                    // Clean up temp file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                }
            }
        }

        $this->command->info('Vendors seeded successfully!');
    }
}