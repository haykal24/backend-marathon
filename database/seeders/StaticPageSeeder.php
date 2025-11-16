<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Tentang Kami',
                'slug' => 'tentang-kami',
                'content' => '<h2>Tentang {{site_name}}</h2>
                    <p>Selamat datang di {{site_name}}. Kami bukan sekadar kalender lari. Kami adalah <strong>ekosistem digital #1</strong> dan pusat otoritas untuk komunitas pelari di Indonesia.</p>
                    <p>Misi kami adalah menjadi jembatan yang menghubungkan pelari dengan event impian mereka, event organizer dengan audiens yang tepat, dan brand dengan komunitas yang loyal.</p>
                    <h3>Apa yang Kami Lakukan?</h3>
                    <h4>1. Untuk Pelari</h4>
                    <p>Kami adalah start line Anda. Kami menyediakan <em>Kalender Lari {{current_year}}</em> dan <em>Jadwal Marathon</em> terlengkap yang didukung oleh visibilitas SEO terbaik. Tidak hanya jadwal, kami juga menyajikan insight dan panduan melalui artikel pilar seperti “Apa Itu Pace?” atau “Panduan Half Marathon” untuk membantu Anda mencapai garis finis.</p>
                    <h4>2. Untuk Event Organizer &amp; Brand</h4>
                    <p>Kami adalah partner pertumbuhan Anda. Melalui platform kami, event atau brand Anda mendapatkan <em>audiens instan &amp; tertarget</em>. Kami menyediakan platform “Kirim Event” yang mudah serta paket promosi premium bagi mereka yang ingin #NaikKelas.</p>
                    <h3>Peran Kami</h3>
                    <p>{{site_name}} adalah platform media independen. Kami <strong>bukan</strong> penyelenggara dari event yang terdaftar di situs ini. Untuk detail pendaftaran atau informasi teknis lainnya, silakan hubungi kontak penyelenggara resmi yang tertera di setiap halaman event.</p>
                    <h3>Kami Membangun Ini Bersama Anda</h3>
                    <ul>
                        <li><strong>Penyelenggara Event:</strong> gunakan tombol “Kirim Event” untuk mempublikasikan event Anda setelah login via WA OTP.</li>
                        <li><strong>Komunitas Pelari:</strong> jika menemukan data yang keliru atau jadwal yang berubah, kirimkan koreksi ke <a href="mailto:{{contact_email}}">{{contact_email}}</a>.</li>
                    </ul>
                    <h3>Tetap Terhubung</h3>
                    <p>Ikuti perjalanan kami dan dapatkan update event terbaru, tips lari, dan info gear di media sosial kami:</p>
                    <p><strong>Instagram:</strong> {{instagram_handle}}</p>
                    <p>Salam lari,<br><strong>Tim {{site_name}}</strong></p>',
                'seo_title' => 'Tentang {{site_name}}',
                'seo_description' => 'Kenali {{site_name}}, ekosistem digital #1 untuk kalender lari, komunitas, dan brand running di Indonesia.',
            ],
            [
                'title' => 'Kontak',
                'slug' => 'kontak',
                'content' => '<h2>Hubungi {{site_name}}</h2>
                    <p>Kami siap membantu Anda untuk pertanyaan, kerjasama, atau dukungan teknis terkait ekosistem lari di Indonesia.</p>
                    <h3>Informasi Kontak</h3>
                    <p><strong>Email:</strong> <a href="mailto:{{contact_email}}">{{contact_email}}</a><br>
                    <strong>WhatsApp:</strong> <a href="https://wa.me/{{contact_whatsapp_digits}}">{{contact_whatsapp}}</a><br>
                    <strong>Alamat:</strong> {{contact_address}}</p>
                    <h3>Jam Operasional</h3>
                    <p>Senin - Jumat: 09.00 - 17.00 WIB<br>
                    Sabtu: 09.00 - 13.00 WIB<br>
                    Minggu: Tutup</p>
                    <h3>Media Sosial</h3>
                    <p>Ikuti update event dan tips lari kami di:<br>
                    Instagram: {{instagram_handle}}<br>
                    Facebook: {{facebook_url}}<br>
                    X (Twitter): {{twitter_handle}}<br>
                    TikTok: {{tiktok_handle}}</p>',
                'seo_title' => 'Kontak {{site_name}}',
                'seo_description' => 'Hubungi tim {{site_name}} untuk kolaborasi, dukungan komunitas, atau pertanyaan seputar ekosistem lari.',
            ],
            [
                'title' => 'Rate Card Overview',
                'slug' => 'rate-card',
                'content' => '<h2>Jangkau Ribuan Pelari. Tepat di Garis Start.</h2>
                    <p><strong>indonesiamarathon.com</strong> bukan sekadar kalender. Kami adalah ekosistem digital #1 dan pusat otoritas untuk komunitas pelari di Indonesia.</p>
                    <p>Kami hadir untuk event dan brand Anda. Saat Anda bermitra dengan kami, Anda tidak hanya membeli slot iklan; Anda berinvestasi pada platform yang dirancang untuk mendominasi visibilitas online.</p>
                    <h3>Mengapa Memilih Kami?</h3>
                    <ul>
                        <li><strong>Visibilitas SEO Maksimal:</strong> Event Anda otomatis muncul di Google saat pelari mencari "jadwal lari", "kalender marathon", atau "event lari {{current_year}}".</li>
                        <li><strong>Audiens Instan &amp; Tertarget:</strong> Terhubung langsung dengan puluhan ribu pelari aktif di komunitas kami yang siap mencari dan mendaftar event baru.</li>
                        <li><strong>Jangkauan Web &amp; Instagram:</strong> Promosi Anda terintegrasi di platform web kami dan di akun Instagram @indonesiamarathon yang terkurasi khusus untuk komunitas lari.</li>
                        <li><strong>Data Performa Transparan:</strong> Pantau klik, impresi, dan konversi pendaftaran event Anda melalui dasbor partner yang transparan.</li>
                    </ul>',
                'seo_title' => 'Rate Card IndonesiaMarathon.com {{current_year}}',
                'seo_description' => 'Pelajari paket iklan dan kolaborasi di indonesiamarathon.com untuk menjangkau komunitas pelari Indonesia setiap tahun.',
            ],
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'privacy',
                'content' => '<h2>Kebijakan Privasi untuk {{site_name}}</h2>
                    <p><strong>Terakhir diperbarui:</strong> {{last_updated}}</p>
                    <p>Selamat datang di {{site_name}}. Privasi pengunjung kami sangat penting bagi kami. Dokumen ini menjelaskan secara rinci jenis informasi pribadi yang dikumpulkan dan dicatat oleh {{site_name}} serta bagaimana kami menggunakannya.</p>
                    <p>Jika Anda memerlukan informasi lebih lanjut atau memiliki pertanyaan tentang kebijakan privasi kami, jangan ragu untuk menghubungi kami melalui email di {{contact_email}}.</p>
                    <h3>1. Informasi yang Kami Kumpulkan</h3>
                    <p>Kami mengumpulkan informasi melalui dua cara: data yang Anda berikan secara langsung dan data yang dikumpulkan secara otomatis saat Anda menggunakan layanan kami.</p>
                    <h4>A. Data yang Anda Berikan Secara Langsung</h4>
                    <ul>
                        <li><strong>Autentikasi Pengguna (Login WA OTP):</strong> Saat Anda mendaftar atau login, kami mengumpulkan nomor telepon Anda untuk mengirimkan OTP via WhatsApp. Kami tidak akan membagikan atau menggunakan nomor tersebut untuk pemasaran tanpa persetujuan Anda.</li>
                        <li><strong>Profil Pengguna (Opsional):</strong> Anda dapat melengkapi profil dengan nama dan alamat email untuk mempersonalisasi pengalaman Anda.</li>
                        <li><strong>Fitur “Kirim Event”:</strong> Data event yang Anda kirimkan (nama event, lokasi, deskripsi, kontak penyelenggara) digunakan untuk kurasi dan penayangan di situs.</li>
                    </ul>
                    <h4>B. Data yang Dikumpulkan Secara Otomatis</h4>
                    <ul>
                        <li><strong>File Log:</strong> Mencakup alamat IP, jenis browser, ISP, cap tanggal/waktu, halaman rujukan/keluar, serta jumlah klik untuk analitik dan pengelolaan situs.</li>
                        <li><strong>Cookies &amp; Web Beacons:</strong> Digunakan untuk menyimpan preferensi pengunjung dan menyesuaikan konten berdasarkan perilaku browsing.</li>
                    </ul>
                    <h3>2. Bagaimana Kami Menggunakan Informasi Anda</h3>
                    <ul>
                        <li>Menyediakan, mengoperasikan, dan memelihara website.</li>
                        <li>Mengautentikasi akun dan mengamankan akses (OTP WhatsApp).</li>
                        <li>Memproses serta menayangkan event yang Anda kirim melalui fitur “Kirim Event”.</li>
                        <li>Menganalisis penggunaan situs untuk meningkatkan layanan.</li>
                        <li>Berkomunikasi dengan Anda untuk layanan pelanggan, pembaruan, serta pemasaran (jika disetujui).</li>
                        <li>Menampilkan iklan yang kami jual langsung kepada mitra (direct ads).</li>
                    </ul>
                    <h3>3. Tautan Afiliasi</h3>
                    <p>Beberapa artikel atau halaman dapat memuat tautan afiliasi. Jika Anda melakukan pembelian setelah menekan tautan tersebut, kami mungkin menerima komisi tanpa biaya tambahan untuk Anda. Kami hanya merekomendasikan produk atau layanan yang relevan bagi komunitas pelari.</p>
                    <h3>4. Keamanan Data</h3>
                    <p>Kami mengambil langkah-langkah wajar untuk melindungi data pribadi Anda dari akses tidak sah. Namun, tidak ada metode transmisi internet atau penyimpanan elektronik yang 100% aman.</p>
                    <h3>5. Privasi Anak-Anak</h3>
                    <p>Situs kami tidak ditujukan untuk anak di bawah 18 tahun. Jika Anda mengetahui anak Anda telah memberikan data pribadi kepada kami, silakan hubungi kami agar data tersebut dapat dihapus.</p>
                    <h3>6. Persetujuan Anda</h3>
                    <p>Dengan menggunakan website kami, Anda menyetujui Kebijakan Privasi ini dan syarat-syaratnya.</p>
                    <h3>7. Perubahan Kebijakan</h3>
                    <p>Kami dapat memperbarui kebijakan privasi sewaktu-waktu. Setiap perubahan akan diumumkan melalui halaman ini dan berlaku sejak tanggal diperbarui.</p>',
                'seo_title' => 'Kebijakan Privasi - {{site_name}}',
                'seo_description' => 'Pelajari bagaimana {{site_name}} mengumpulkan, menggunakan, dan melindungi data pribadi Anda.',
            ],
        ];

        foreach ($pages as $pageData) {
            StaticPage::firstOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }

        $this->command->info('Static pages seeded successfully!');
    }
}