<?php

namespace Database\Seeders;

use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Tags\Tag;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Create Authors
        $authors = [
            [
                'name' => 'Marathon Indonesia Team',
                'email' => 'editor@marathonindonesia.com',
                'bio' => 'Tim editorial Marathon Indonesia yang berdedikasi menyediakan konten berkualitas tentang dunia lari.',
                'github_handle' => null,
                'twitter_handle' => '@marathonindonesia',
            ],
            [
                'name' => 'Ahmad Runners',
                'email' => 'ahmad.runners@marathonindonesia.com',
                'bio' => 'Pelari berpengalaman dan penulis konten tentang tips dan trik lari.',
                'github_handle' => null,
                'twitter_handle' => '@ahmaddrunners',
            ],
        ];

        $authorIds = [];
        foreach ($authors as $authorData) {
            $author = BlogAuthor::firstOrCreate(
                ['email' => $authorData['email']],
                $authorData
            );
            $authorIds[] = $author->id;
        }

        // Create Categories
        $categories = [
            [
                'name' => 'Latihan Lari',
                'slug' => 'latihan-lari',
                'description' => 'Program latihan marathon, teknik lari, dan tips meningkatkan performa',
                'is_visible' => true,
            ],
            [
                'name' => 'Perlengkapan Lari',
                'slug' => 'perlengkapan-lari',
                'description' => 'Review sepatu lari, jam tangan running, dan gear lari berkualitas',
                'is_visible' => true,
            ],
            [
                'name' => 'Nutrisi & Kesehatan',
                'slug' => 'nutrisi-kesehatan',
                'description' => 'Panduan nutrisi, makanan untuk pelari, dan tips kesehatan',
                'is_visible' => true,
            ],
            [
                'name' => 'Komunitas',
                'slug' => 'komunitas',
                'description' => 'Kisah inspiratif dan cerita dari komunitas lari Indonesia',
                'is_visible' => true,
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $categoryData) {
            $category = BlogCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
            $categoryIds[$category->slug] = $category->id;
        }

        // --- Sample General Posts ---
        $samplePosts = [
            [
                'title' => '10 Tips untuk Pemula yang Ingin Mulai Lari',
                'slug' => '10-tips-untuk-pemula-yang-ingin-mulai-lari',
                'excerpt' => 'Panduan lengkap untuk pemula yang baru memulai hobi lari. Pelajari tips dasar untuk menghindari cedera dan meningkatkan performa.',
                'content' => '<h2>Pendahuluan</h2>
                    <p>Lari adalah salah satu olahraga yang paling mudah dilakukan dan tidak memerlukan banyak peralatan. Namun, untuk pemula, ada beberapa hal penting yang perlu diperhatikan agar aktivitas lari menjadi menyenangkan dan aman.</p>
                    
                    <h3>1. Mulai dengan Perlahan</h3>
                    <p>Jangan langsung memaksakan diri untuk lari jarak jauh. Mulailah dengan jogging ringan atau kombinasi jalan dan lari selama 20-30 menit.</p>
                    
                    <h3>2. Pilih Sepatu yang Tepat</h3>
                    <p>Investasi terbaik untuk pelari adalah sepatu lari yang berkualitas. Pilih sepatu yang sesuai dengan tipe kaki dan gaya lari Anda.</p>
                    
                    <h3>3. Pemanasan dan Pendinginan</h3>
                    <p>Selalu lakukan pemanasan sebelum lari dan pendinginan setelahnya untuk mencegah cedera.</p>
                    
                    <h3>4. Tetapkan Target yang Realistis</h3>
                    <p>Mulai dengan target kecil dan tingkatkan secara bertahap. Jangan terburu-buru ingin langsung ikut marathon.</p>
                    
                    <h3>5. Dengarkan Tubuh Anda</h3>
                    <p>Jika merasa sakit atau lelah berlebihan, istirahatlah. Jangan memaksakan diri.</p>
                    
                    <h3>6. Hidrasi yang Cukup</h3>
                    <p>Pastikan minum air yang cukup sebelum, selama, dan setelah lari.</p>
                    
                    <h3>7. Nutrisi yang Seimbang</h3>
                    <p>Konsumsi makanan bergizi untuk mendukung aktivitas lari Anda.</p>
                    
                    <h3>8. Bergabung dengan Komunitas</h3>
                    <p>Bergabung dengan komunitas lari akan memberikan motivasi dan dukungan.</p>
                    
                    <h3>9. Track Progress</h3>
                    <p>Gunakan aplikasi atau watch untuk melacak progres lari Anda.</p>
                    
                    <h3>10. Nikmati Prosesnya</h3>
                    <p>Yang terpenting, nikmati setiap langkah dan jadikan lari sebagai aktivitas yang menyenangkan.</p>
                    
                    <h2>Kesimpulan</h2>
                    <p>Mulai lari tidak perlu rumit. Mulai dari yang kecil, konsisten, dan nikmati perjalanannya. Selamat berlari!</p>',
                'blog_author_id' => $authorIds[0] ?? 1,
                'blog_category_id' => $categoryIds[0] ?? 1,
                'published_at' => now()->subDays(5),
                'tags' => ['tips lari', 'pemula', 'training plan'],
            ],
            [
                'title' => 'Review Event: Jakarta Marathon 2025',
                'slug' => 'review-event-jakarta-marathon-2025',
                'excerpt' => 'Ulasan lengkap tentang Jakarta Marathon 2025, termasuk rute, fasilitas, dan pengalaman peserta.',
                'content' => '<h2>Review Jakarta Marathon 2025</h2>
                    <p>Jakarta Marathon 2025 kembali digelar dengan antusiasme tinggi dari para pelari. Event yang diadakan pada tanggal 15 Oktober 2025 ini menghadirkan berbagai kategori lari mulai dari 5K, 10K, 21K, hingga 42K.</p>
                    
                    <h3>Rute dan Medali</h3>
                    <p>Rute yang digunakan sangat menarik, melewati landmark penting di Jakarta. Medali yang diberikan juga berkualitas tinggi dengan desain yang menarik.</p>
                    
                    <h3>Fasilitas Event</h3>
                    <p>Event ini menyediakan berbagai fasilitas lengkap termasuk water station yang cukup, medical support, dan fotografer profesional.</p>
                    
                    <h3>Kesimpulan</h3>
                    <p>Jakarta Marathon 2025 adalah event yang sangat direkomendasikan untuk pelari dari berbagai level.</p>',
                'blog_author_id' => $authorIds[0] ?? 1,
                'blog_category_id' => $categoryIds[1] ?? 2,
                'published_at' => now()->subDays(3),
                'tags' => ['review event', 'jakarta marathon', 'race report'],
            ],
            [
                'title' => 'Panduan Memilih Sepatu Lari yang Tepat',
                'slug' => 'panduan-memilih-sepatu-lari-yang-tepat',
                'excerpt' => 'Panduan lengkap untuk memilih sepatu lari yang sesuai dengan kebutuhan dan tipe kaki Anda.',
                'content' => '<h2>Memilih Sepatu Lari yang Tepat</h2>
                    <p>Sepatu adalah investasi terpenting untuk pelari. Memilih sepatu yang tepat akan membantu mencegah cedera dan meningkatkan performa.</p>
                    
                    <h3>1. Kenali Tipe Kaki Anda</h3>
                    <p>Ada tiga tipe kaki: neutral, pronasi, dan supinasi. Kenali tipe kaki Anda sebelum membeli sepatu.</p>
                    
                    <h3>2. Pilih Ukuran yang Tepat</h3>
                    <p>Sepatu lari sebaiknya memiliki ruang sekitar setengah inci di depan jari kaki untuk kenyamanan saat lari.</p>
                    
                    <h3>3. Pertimbangkan Permukaan Lari</h3>
                    <p>Sepatu untuk road running berbeda dengan trail running. Pilih sesuai dengan permukaan yang sering Anda gunakan.</p>
                    
                    <h3>4. Kualitas vs Harga</h3>
                    <p>Investasi pada sepatu berkualitas akan menghemat biaya perawatan medis akibat cedera.</p>
                    
                    <h2>Kesimpulan</h2>
                    <p>Jangan ragu untuk berkonsultasi dengan ahli di toko sepatu olahraga untuk mendapatkan sepatu yang tepat.</p>',
                'blog_author_id' => $authorIds[1] ?? 2,
                'blog_category_id' => $categoryIds[2] ?? 3,
                'published_at' => now()->subDays(1),
                'tags' => ['gear review', 'sepatu lari', 'perlengkapan'],
            ],
            [
                'title' => 'Program Training 5K untuk Pemula',
                'slug' => 'program-training-5k-untuk-pemula',
                'excerpt' => 'Program latihan 8 minggu untuk pemula yang ingin menyelesaikan lari 5K dengan baik.',
                'content' => '<h2>Program Training 5K untuk Pemula</h2>
                    <p>Program latihan ini dirancang untuk pemula yang ingin menyelesaikan lari 5K dalam 8 minggu.</p>
                    
                    <h3>Minggu 1-2: Foundation Building</h3>
                    <p>Mulai dengan kombinasi jalan dan lari ringan. Target: 3x seminggu, 20-30 menit per sesi.</p>
                    
                    <h3>Minggu 3-4: Building Endurance</h3>
                    <p>Tingkatkan durasi lari dan kurangi waktu jalan. Target: 3x seminggu, 30-40 menit per sesi.</p>
                    
                    <h3>Minggu 5-6: Increasing Distance</h3>
                    <p>Fokus pada peningkatan jarak. Target: 3-4x seminggu, mulai lari 3-4 km.</p>
                    
                    <h3>Minggu 7-8: Race Preparation</h3>
                    <p>Latihan intensif dan simulasi race. Target: 4x seminggu, sesi long run hingga 5K.</p>
                    
                    <h2>Tips Penting</h2>
                    <ul>
                        <li>Selalu lakukan pemanasan dan pendinginan</li>
                        <li>Istirahat yang cukup antara sesi latihan</li>
                        <li>Jaga hidrasi dan nutrisi</li>
                        <li>Dengarkan tubuh Anda</li>
                    </ul>',
                'blog_author_id' => $authorIds[1] ?? 2,
                'blog_category_id' => $categoryIds[3] ?? 4,
                'published_at' => now(),
                'tags' => ['training plan', 'latihan 5k', 'pemula'],
            ],
            [
                'title' => 'Kisah Inspiratif: Dari Tidak Bisa Lari 1K Hingga Menyelesaikan Marathon',
                'slug' => 'kisah-inspiratif-dari-tidak-bisa-lari-1k-hingga-menyelesaikan-marathon',
                'excerpt' => 'Kisah inspiratif seorang pelari yang berhasil mengubah hidupnya melalui olahraga lari.',
                'content' => '<h2>Kisah Inspiratif</h2>
                    <p>Ini adalah kisah tentang seseorang yang memulai perjalanan lari dari titik nol hingga berhasil menyelesaikan marathon pertamanya.</p>
                    
                    <h3>Awal Perjalanan</h3>
                    <p>Semuanya dimulai dari keputusan sederhana: ingin hidup lebih sehat. Dari tidak bisa lari 1 kilometer, dengan tekad dan konsistensi, perlahan-lahan kemampuan meningkat.</p>
                    
                    <h3>Tantangan yang Dihadapi</h3>
                    <p>Perjalanan tidak selalu mulus. Ada cedera, rasa lelah, dan momen-momen ingin menyerah. Tapi dengan dukungan komunitas dan tekad yang kuat, semua tantangan bisa diatasi.</p>
                    
                    <h3>Pencapaian</h3>
                    <p>Setelah 2 tahun berlatih, akhirnya berhasil menyelesaikan marathon pertama. Ini adalah bukti bahwa dengan konsistensi dan tekad, tidak ada yang tidak mungkin.</p>
                    
                    <h2>Pesan</h2>
                    <p>Jika mereka bisa, Anda juga bisa. Mulai dari langkah kecil, konsisten, dan nikmati perjalanannya.</p>',
                'blog_author_id' => $authorIds[0] ?? 1,
                'blog_category_id' => $categoryIds[3] ?? 4,
                'published_at' => now()->subDays(2),
                'tags' => ['kisah inspiratif', 'marathon', 'komunitas'],
            ],
        ];

        // --- SEO Pillar Pages ---
        $pillarPages = [
            [
                'title' => 'Panduan Marathon Lengkap: Apa Itu Marathon dan Berapa Jarak Larinya?',
                'slug' => 'apa-itu-marathon-jarak-lari',
                'excerpt' => 'Pelajari semua tentang marathon, mulai dari jarak resmi 42,195 kilometer, sejarahnya, hingga perbedaan dengan half marathon. Panduan esensial untuk calon marathoner.',
                'content' => '<p>Marathon adalah perlombaan lari jarak jauh dengan jarak resmi 42,195 kilometer. Artikel ini membahas tuntas apa itu marathon, sejarahnya, dan berapa jarak lari half marathon. Cocok untuk Anda yang ingin menaklukkan marathon pertama.</p>',
                'blog_category_id' => $categoryIds['latihan-lari'],
                'tags' => ['marathon', 'apa itu marathon', 'marathon berapa km', 'half marathon'],
            ],
            [
                'title' => 'Pace Adalah: Panduan Lengkap Cara Menghitung Pace Lari 5K, 10K, dst.',
                'slug' => 'pace-adalah-cara-menghitung',
                'excerpt' => 'Pace adalah kunci untuk mengukur dan meningkatkan performa lari Anda. Pelajari cara menghitung pace lari, artinya, dan tabel pace ideal untuk 5K, 10K, hingga marathon.',
                'content' => '<p>Pace adalah istilah yang sering didengar dalam dunia lari, yang berarti kecepatan rata-rata per kilometer. Memahami pace sangat penting untuk mengatur strategi lari dan mencapai target waktu. Artikel ini akan memandu Anda cara menghitungnya.</p>',
                'blog_category_id' => $categoryIds['latihan-lari'],
                'tags' => ['pace adalah', 'artinya pace', 'cara menghitung pace', 'latihan lari'],
            ],
            [
                'title' => 'Fun Run Artinya Apa? Ini Bedanya dengan Event Lari Biasa',
                'slug' => 'fun-run-artinya',
                'excerpt' => 'Apa itu fun run dan apa bedanya dengan event lari kompetitif seperti marathon? Temukan jawabannya di sini, cocok untuk Anda yang mencari event lari santai tanpa tekanan waktu.',
                'content' => '<p>Fun run adalah event lari yang lebih menekankan pada kesenangan dan partisipasi daripada kompetisi. Biasanya jaraknya lebih pendek (sekitar 5K) dan seringkali memiliki tema unik. Ini adalah cara yang bagus untuk memulai kebiasaan lari.</p>',
                'blog_category_id' => $categoryIds['komunitas'],
                'tags' => ['fun run', 'fun run artinya', 'event lari'],
            ],
            [
                'title' => 'Cut Off Adalah: Pahami Batas Waktu (COT) dalam Event Lari Marathon',
                'slug' => 'cut-off-adalah',
                'excerpt' => 'Memahami apa itu Cut Off Time (COT) sangat penting sebelum Anda mendaftar event lari jarak jauh. Pelajari mengapa COT ada dan tips agar Anda bisa finish sebelum batas waktu.',
                'content' => '<p>Cut Off Time (COT) adalah batas waktu maksimal yang ditetapkan oleh penyelenggara event bagi peserta untuk menyelesaikan perlombaan. Jika Anda melewati batas ini, Anda mungkin akan didiskualifikasi (DNF - Did Not Finish). Penting untuk melatih pace Anda agar sesuai dengan COT.</p>',
                'blog_category_id' => $categoryIds['latihan-lari'],
                'tags' => ['cut off adalah', 'cut off time', 'marathon', 'DNF'],
            ],
            [
                'title' => 'Panduan Trail Running untuk Pemula: Apa Itu Trail Run?',
                'slug' => 'panduan-trail-running-pemula',
                'excerpt' => 'Tertarik lari di alam bebas? Pelajari apa itu trail running, perbedaannya dengan road running, serta tips dan perlengkapan esensial untuk memulai petualangan trail run pertama Anda.',
                'content' => '<p>Trail running adalah aktivitas lari yang dilakukan di jalur alam seperti pegunungan, hutan, atau perbukitan. Medan yang tidak rata memberikan tantangan yang berbeda dan membutuhkan sepatu serta teknik khusus. Ini adalah cara yang luar biasa untuk menikmati alam sambil berolahraga.</p>',
                'blog_category_id' => $categoryIds['latihan-lari'],
                'tags' => ['trail run', 'trail running', 'lari lintas alam', 'pemula'],
            ],
        ];

        foreach ($pillarPages as &$page) {
            $page['blog_author_id'] = $authorIds[0] ?? 1;
            $page['published_at'] = now();
        }

        // Gabungkan sample posts dengan pillar pages
        $posts = array_merge($samplePosts, $pillarPages);
        
        $placeholderPath = public_path('seeders/placeholder.jpg');
        $bannerDirectory = storage_path('app/public/blog');
        if (! File::exists($bannerDirectory)) {
            File::makeDirectory($bannerDirectory, 0755, true);
        }

        foreach ($posts as $postData) {
            $tags = $postData['tags'] ?? [];
            unset($postData['tags']);

            $post = BlogPost::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );

            // Attach placeholder image jika post belum punya banner
            if ($post->banner == null && File::exists($placeholderPath)) {
                $fileName = Str::slug($post->slug) . '-placeholder.jpg';
                $destination = $bannerDirectory . '/' . $fileName;
                File::copy($placeholderPath, $destination);

                $post->banner = 'blog/' . $fileName;
                $post->save();
            }

            if (! empty($tags)) {
                // Ensure tags are created with proper name and slug
                $tagModels = [];
                foreach ($tags as $tagName) {
                    if (empty($tagName) || trim($tagName) === '') {
                        continue;
                    }
                    
                    $tagName = trim($tagName);
                    $tagSlug = Str::slug($tagName);
                    
                    // Find or create tag with proper name and slug
                    $tag = Tag::firstOrCreate(
                        ['slug' => $tagSlug],
                        ['name' => $tagName]
                    );
                    
                    // Ensure name and slug are set (in case tag exists but has empty values)
                    if (empty($tag->name) || empty($tag->slug)) {
                        $tag->name = $tagName;
                        $tag->slug = $tagSlug;
                        $tag->save();
                    }
                    
                    $tagModels[] = $tag;
                }
                
                // Sync tags using models instead of strings
                if (! empty($tagModels)) {
                    $post->syncTags($tagModels);
                }
            }
        }

        $this->command->info('✓ Blog authors seeded: ' . count($authorIds));
        $this->command->info('✓ Blog categories seeded: ' . count($categoryIds));
        $this->command->info('✓ Blog posts seeded: ' . count($posts));
    }
}