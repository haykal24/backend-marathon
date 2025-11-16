<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds - FAQ dengan keyword research
     */
    public function run(): void
    {
        $faqs = [
            // ========== MARATHON CATEGORY ==========
            [
                'category' => 'marathon',
                'keyword' => 'marathon',
                'question' => 'Apa itu marathon?',
                'answer' => 'Marathon adalah lari jarak panjang sejauh 42,195 kilometer. Event ini merupakan salah satu cabang olahraga lari paling populer di dunia dengan sejarah panjang sejak zaman Yunani Kuno. Marathon menguji daya tahan, mental, dan persiapan fisik pelari. Untuk lari marathon yang aman, Anda perlu program latihan minimal 16-20 minggu dengan periodisasi yang tepat.',
                'sort_order' => 1,
                'is_active' => true,
                'related_keyword' => 'half_marathon',
            ],
            [
                'category' => 'marathon',
                'keyword' => 'marathon',
                'question' => 'Marathon berapa kilometer?',
                'answer' => 'Marathon standar adalah 42,195 kilometer atau sering dibulatkan menjadi 42.2 km. Jarak ini berasal dari kisah sejarah di Yunani Kuno, ketika seorang prajurit lari dari kota Marathon ke Athena untuk melaporkan kemenangan. Dalam event marathon resmi yang diakui oleh World Athletics, jarak harus tepat 42.195 km sesuai standar internasional.',
                'sort_order' => 2,
                'is_active' => true,
                'related_keyword' => 'half_marathon',
            ],
            [
                'category' => 'marathon',
                'keyword' => 'half_marathon',
                'question' => 'Half marathon berapa kilometer?',
                'answer' => 'Half marathon atau setengah marathon adalah 21,0975 kilometer atau sering dibulatkan menjadi 21.1 km. Jarak ini tepat setengah dari marathon standar 42.195 km. Half marathon menjadi pilihan populer bagi pelari yang ingin tantangan lebih besar dari 10K namun belum siap full marathon. Program latihan untuk half marathon biasanya memerlukan 8-12 minggu persiapan.',
                'sort_order' => 3,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'marathon',
                'keyword' => 'marathon',
                'question' => 'Bagaimana cara latihan marathon yang tepat?',
                'answer' => 'Latihan marathon memerlukan pendekatan sistematis dengan fase-fase persiapan. Mulai dengan base building (4-6 minggu) untuk membangun fondasi. Dilanjutkan dengan build-up phase (8-10 minggu) menaikkan volume dan intensitas. Sertakan long runs mingguan, tempo runs, interval training, dan rest days. Penting juga melakukan cross-training dan strength training untuk mencegah cedera. Pastikan juga menjalankan tapering 2 minggu sebelum race day.',
                'sort_order' => 4,
                'is_active' => true,
                'related_keyword' => 'pace',
            ],

            // ========== PACE CATEGORY ==========
            [
                'category' => 'pace',
                'keyword' => 'pace',
                'question' => 'Apa itu pace lari?',
                'answer' => 'Pace adalah kecepatan lari yang biasanya dinyatakan dalam satuan menit per kilometer (min/km) atau menit per mil (min/mile). Pace menunjukkan berapa lama waktu yang dibutuhkan untuk menempuh satu kilometer. Misalnya, pace 6 min/km berarti Anda memerlukan 6 menit untuk lari sejauh 1 kilometer. Pace sangat penting untuk merencanakan target waktu dan strategi race.',
                'sort_order' => 5,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'pace',
                'keyword' => 'pace',
                'question' => 'Bagaimana cara menghitung pace lari?',
                'answer' => 'Pace lari dihitung dengan rumus: Total Waktu (menit) ÷ Total Jarak (km) = Pace (min/km). Contoh: Jika Anda lari 10 km dalam 60 menit, maka pace Anda adalah 60 ÷ 10 = 6 min/km. Untuk menghitung pace untuk marathon, misalnya target finish 4 jam (240 menit) untuk 42.195 km, maka pace yang dibutuhkan adalah 240 ÷ 42.195 = 5:41 per km. Banyak aplikasi dan GPS watch modern yang menghitung pace secara otomatis saat Anda lari.',
                'sort_order' => 6,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'pace',
                'keyword' => 'pace',
                'question' => 'Pace ideal untuk marathon berapa?',
                'answer' => 'Pace ideal untuk marathon tergantung level pelari. Pemula target 6:30-8:00 min/km (finish 4:30-5:30 jam), Menengah target 5:30-6:30 min/km (finish 3:55-4:30 jam), Lanjutan target 4:30-5:30 min/km (finish 3:10-3:55 jam), Elite target <4:30 min/km (finish <3:10 jam). Penting untuk memilih pace yang realistis sesuai persiapan dan kondisi fisik. Pace yang terlalu kencang berisiko cedera dan bonk, terlalu lambat bisa melewati cut-off time.',
                'sort_order' => 7,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],

            // ========== CUT OFF CATEGORY ==========
            [
                'category' => 'cut_off',
                'keyword' => 'cut_off',
                'question' => 'Apa itu cut off lari?',
                'answer' => 'Cut off (COT) adalah batas waktu maksimal untuk menyelesaikan lari dalam event. Setiap titik cut-off harus dilewati dalam waktu yang ditentukan. Misalnya cut-off di KM 21 adalah 2:30 jam, artinya Anda harus mencapai KM 21 dalam waktu maksimal 2:30 jam dari start. Jika tidak mencapai titik cut-off dalam waktu yang ditentukan, Anda akan disebut DNF (Did Not Finish) dan tidak boleh lanjut lari.',
                'sort_order' => 8,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'cut_off',
                'keyword' => 'cut_off',
                'question' => 'Apa yang terjadi jika melewati cut off?',
                'answer' => 'Jika Anda melewati cut-off time di suatu titik, Anda akan disebut DNF (Did Not Finish) dan tidak diperkenankan melanjutkan lari. Panitia biasanya akan meminta Anda keluar dari jalur lari untuk alasan keselamatan dan pengelolaan event. Anda juga tidak akan mendapatkan finisher medal atau sertifikat. Meskipun mengecewakan, cut-off diterapkan untuk keselamatan peserta dan efisiensi pengelolaan event terutama untuk marathon dengan peserta ribuan.',
                'sort_order' => 9,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'cut_off',
                'keyword' => 'cut_off',
                'question' => 'Bagaimana strategi agar tidak kena cut off?',
                'answer' => 'Strategi menghindari cut-off: 1) Ketahui cut-off time sebelum race dan hitung pace yang dibutuhkan. 2) Jangan start terlalu cepat, pertahankan pace yang konsisten dan sustainable. 3) Manfaatkan aid station dengan bijak tanpa menghabiskan terlalu banyak waktu. 4) Jika mulai tertinggal, fokus mencapai next cut-off point, jangan langsung panik. 5) Persiapan latihan yang matang agar punya buffer waktu. 6) Konsumsi nutrisi dan hidrasi yang optimal agar tidak bonk. Ingat, lebih baik finisher dengan pace lambat daripada DNF.',
                'sort_order' => 10,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],

            // ========== FUN RUN CATEGORY ==========
            [
                'category' => 'fun_run',
                'keyword' => 'fun_run',
                'question' => 'Apa itu fun run?',
                'answer' => 'Fun run adalah event lari yang diselenggarakan dengan tujuan utama kesenangan dan kebugaran daripada kompetisi. Peserta lari dengan santai, sering sambil bercanda atau foto-foto. Tidak ada tekanan untuk mencapai waktu tertentu atau juara, semua bisa finish sesuai kemampuan masing-masing. Fun run cocok untuk pemula, keluarga, dan siapa saja yang ingin olahraga sambil bersenang-senang. Jarak fun run biasanya 3-5 km dan ada banyak hiburan, hadiah doorprize, dan community vibe yang menyenangkan.',
                'sort_order' => 11,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'fun_run',
                'keyword' => 'fun_run',
                'question' => 'Perbedaan fun run dan marathon apa?',
                'answer' => 'Perbedaan utama: 1) Jarak: Fun run 3-5 km vs Marathon 42.195 km. 2) Tujuan: Fun run santai dan sosial vs Marathon kompetitif. 3) Persiapan: Fun run minimal latihan vs Marathon butuh 16-20 minggu latihan. 4) Tekanan: Fun run santai, no cut-off vs Marathon ada cut-off, kompetitif. 5) Biaya: Fun run murah 50-100K vs Marathon mahal 200-500K+. 6) Waktu: Fun run 30-45 menit vs Marathon 3-5+ jam. Fun run ideal untuk memulai lari, marathon untuk yang serius dengan lari.',
                'sort_order' => 12,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],

            // ========== TRAIL RUN CATEGORY ==========
            [
                'category' => 'trail_run',
                'keyword' => 'trail_run',
                'question' => 'Apa itu trail run?',
                'answer' => 'Trail run adalah lari di alam terbuka di jalur/trek yang tidak diaspal, bisa di gunung, hutan, atau area pegunungan. Berbeda dari road run yang di jalan aspal, trail run menantang karena medan tidak rata, ada tanjakan, turunan, batu, akar, dan kondisi tanah yang berubah-ubah. Trail run memerlukan fokus tinggi, teknik khusus, dan perlengkapan khusus seperti trail shoes dengan grip baik. Trail run populer bagi yang mencari tantangan dan petualangan di alam sambil olahraga.',
                'sort_order' => 13,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'trail_run',
                'keyword' => 'trail_run',
                'question' => 'Perbedaan trail run dan road run apa?',
                'answer' => 'Perbedaan utama: 1) Lokasi: Trail run di alam/gunung vs Road run di jalan aspal. 2) Medan: Trail run tidak rata, berbukit vs Road run rata. 3) Teknik: Trail run perlu teknik khusus, langkah pendek vs Road run teknik standar. 4) Sepatu: Trail run perlu trail shoes grip tinggi vs Road run cukup running shoes biasa. 5) Kecepatan: Trail run lebih lambat karena medan vs Road run bisa lebih cepat. 6) Risiko: Trail run risiko cedera higher vs Road run lebih aman. 7) Pengalaman: Trail run petualangan di alam vs Road run olahraga perkotaan. Trail run untuk yang suka tantangan dan alam, road run untuk performa fokus waktu.',
                'sort_order' => 14,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],
            [
                'category' => 'trail_run',
                'keyword' => 'trail_run',
                'question' => 'Tips trail running untuk pemula apa saja?',
                'answer' => '1) Mulai dengan trail pendek 5-10 km. 2) Gunakan trail shoes dengan grip traction baik. 3) Latih teknik, fokus pada langkah pendek, postur tegak. 4) Lihat jalur ahead, jangan langsung matanya ke kaki. 5) Tidak perlu cepat, safety dan enjoyment prioritas. 6) Lari dengan teman untuk safety. 7) Bawa cukup air dan snack. 8) Tahu lokasi, bawa peta atau GPS. 9) Pakai sunscreen, topi, pakaian tahan panas. 10) Cross-training dan strength training untuk stabil dan cegah cedera. 11) Gradual increase distance dan difficulty. 12) Dengarkan body, istirahat jika capek atau sakit.',
                'sort_order' => 15,
                'is_active' => true,
                'related_keyword' => 'marathon',
            ],

            // ========== GENERAL/LOCATION CATEGORIES ==========
            [
                'category' => 'event',
                'keyword' => null,
                'question' => 'Bagaimana cara mendaftar event lari?',
                'answer' => 'Langkah mendaftar event lari: 1) Cari event di IndonesiaMarathon.com atau website event. 2) Pilih kategori jarak (5K, 10K, half marathon, marathon). 3) Klik tombol "Daftar" dan ikuti link pendaftaran. 4) Isi data pribadi lengkap dan data emergency. 5) Pilih kategori usia atau kategori khusus lainnya. 6) Bayar biaya pendaftaran via transfer bank, e-wallet, atau payment gateway. 7) Upload bukti pembayaran jika diminta. 8) Tunggu konfirmasi dari panitia. 9) Ambil race kit (bib, running kit) sebelum race hari. Pastikan daftar sebelum kuota penuh dan jangan tunggu hari terakhir.',
                'sort_order' => 16,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'event',
                'keyword' => null,
                'question' => 'Apa saja yang perlu disiapkan sebelum event lari?',
                'answer' => 'Persiapan sebelum event: 1) Latihan teratur minimal 4-6 minggu sesuai jarak. 2) Siapkan gear: running shoes, pakaian, bib, timing chip. 3) Check kesehatan ke dokter. 4) Buat race plan dan hitung pace target. 5) Pelajari rute dan lokasi. 6) Persiapkan nutrisi: carbo loading hari sebelum, sarapan ringan hari race. 7) Set alarm cukup pagi, jangan bangun terlalu pagi. 8) Siapkan hydration plan, tahu lokasi aid station. 9) Lakukan dynamic warm-up 10 menit sebelum start. 10) Rest cukup hari sebelum, jangan aktivitas berat. 11) Mental preparation, visualisasi finish. 12) Emergency plan jika ada masalah saat lari.',
                'sort_order' => 17,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'event',
                'keyword' => null,
                'question' => 'Apa saja benefit peserta dalam event lari?',
                'answer' => 'Benefit peserta yang biasanya didapat: 1) Finisher medal sebagai kenang-kenangan. 2) Running kit/jersey official event. 3) Bib dan timing chip untuk hasil resmi. 4) Race bag atau goodie bag berisi merchandise. 5) Voucher atau diskon dari sponsor. 6) Sertifikat finisher official. 7) Official race photo (gratis atau bisa dibeli). 8) Hasil ranking resmi di website event. 9) Medical tent dan first aid gratis. 10) Hydration dan fuel di aid station. 11) Community engagement dan running family. 12) Kebanggan dan achievement personal. Benefit tergantung event, kategori peserta, dan sponsor.',
                'sort_order' => 18,
                'is_active' => true,
                'related_keyword' => null,
            ],

            // ========== TENTANG INDONESIAMARATHON.COM ==========
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Apa itu IndonesiaMarathon.com?',
                'answer' => 'IndonesiaMarathon.com adalah platform digital #1 untuk kalender lari dan ekosistem komunitas pelari di Indonesia. Kami menyediakan: 1) Jadwal lengkap event lari (marathon, half marathon, fun run, trail run) di seluruh Indonesia. 2) Direktori vendor terpercaya (medali, perlengkapan, race management). 3) Daftar komunitas lari aktif di berbagai kota. 4) Artikel edukatif tentang lari, latihan, dan tips marathon. 5) Platform submit event untuk EO dan komunitas. 6) Info promo dan kolaborasi dengan brand. Tujuan kami: memberdayakan komunitas pelari Indonesia dengan informasi terlengkap, terpercaya, dan terakses.',
                'sort_order' => 19,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Bagaimana cara submit event ke IndonesiaMarathon.com?',
                'answer' => 'Langkah submit event: 1) Buat akun dengan login WhatsApp OTP di platform kami (no password diperlukan). 2) Klik tombol "Submit Event" di menu utama. 3) Isi form event: judul, deskripsi, tanggal, lokasi, jenis event, kategori, biaya registrasi, benefit peserta, kontak organizer, media sosial. 4) Upload foto/poster event (otomatis dikonversi ke WebP). 5) Klik "Submit" dan tunggu review admin. 6) Event akan ditampilkan di kalender lari setelah disetujui. Admin biasanya review dalam 1-2 hari kerja. Syarat: event harus legal, info lengkap, dan tidak melanggar aturan.',
                'sort_order' => 20,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Bagaimana cara mencari event lari di IndonesiaMarathon.com?',
                'answer' => 'Mencari event lari sangat mudah: 1) Masuk ke halaman "Jadwal Lari" atau "Event". 2) Gunakan filter pencarian: search by name, filter by jenis event (marathon, fun run, dll), filter by kota/provinsi, filter by bulan/tahun. 3) Pilih tampilan: Card view (visual) atau Table view (data padat). 4) Klik event yang menarik untuk lihat detail lengkap: deskripsi, lokasi, benefit, biaya, kontak. 5) Klik "Daftar" untuk pergi ke link pendaftaran resmi. Alternatif: browse "Artikel" untuk panduan dan tips marathon, atau cari di "Direktori" untuk vendor terpercaya.',
                'sort_order' => 21,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Apakah IndonesiaMarathon.com gratis untuk semua?',
                'answer' => 'Ya, IndonesiaMarathon.com gratis untuk semua pengguna: 1) Browsing event, artikel, dan direktori: 100% gratis. 2) Login dan submit event: gratis. 3) Daftar newsletter komunitas: gratis. Namun ada opsi berbayar: 1) Premium listing di direktori (vendor, komunitas yang ingin featured). 2) Sponsor kolaborasi dengan ad banner di homepage. 3) Rate card untuk EO yang ingin featured di event pilihan. Untuk info lebih lanjut tentang paket premium, klik "Rate Card" di menu atau hubungi tim kami melalui halaman "Kontak".',
                'sort_order' => 22,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Bagaimana kalau event saya sudah submit tapi belum muncul?',
                'answer' => 'Jika event Anda belum muncul setelah submit: 1) Pastikan email/WhatsApp yang digunakan login masih aktif (kami kirim notifikasi perubahan status). 2) Cek status di profil akun Anda - ada field "Status Event" yang menunjukkan: Draft (belum submit), Pending Review (menunggu persetujuan admin), atau Published (sudah live). 3) Jika masih Pending Review, tunggu 1-2 hari kerja. Admin review manual untuk memastikan data valid. 4) Jika status berubah jadi Rejected/Draft, lihat feedback dari admin dan perbaiki sesuai catatan. 5) Jika ada masalah teknis, hubungi support@indonesiamarathon.com dengan screenshot status dan detail event. Kami siap bantu ASAP.',
                'sort_order' => 23,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Apakah bisa edit atau hapus event yang sudah di-publish?',
                'answer' => 'Tergantung status event: 1) Event dalam status Draft atau Pending Review: Bisa diedit/dihapus sendiri dari profil akun. 2) Event yang sudah Published (live): Tidak bisa dihapus sendiri, tapi bisa minta edit/revisi ke admin via support. Alasan: data event sudah tersebar di kalender, RSS, dan search engine. Edit akan melalui review ulang. 3) Untuk perubahan kecil (kontak, media sosial): hubungi support, kami proses cepat. 4) Untuk perubahan besar (jadwal, lokasi): buat event baru atau hubungi admin untuk re-review. Tips: pastikan data lengkap dan akurat sebelum submit untuk menghindari banyak revisi.',
                'sort_order' => 24,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Siapa boleh submit event ke IndonesiaMarathon.com?',
                'answer' => 'Siapa saja boleh submit event: 1) Event Organizer (EO) resmi: submit event kompetisi/marathon mereka. 2) Komunitas lari: submit event gathering/fun run komunitas. 3) Brand/sponsor: submit event brand activation yang terkait lari. 4) Individu/grup: submit event informal dengan catatan tetap aman dan legal. Syarat submit: 1) Harus punya akun (login via WhatsApp OTP). 2) Data event lengkap dan valid. 3) Event harus legal dan sesuai regulasi lokal. 4) Event tidak melanggar hak pihak lain. 5) Submit minimal 1-2 minggu sebelum event untuk proses review. Catatan: EO besar lebih disarankan pakai premium listing agar event lebih visible di kalender kami.',
                'sort_order' => 25,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Bagaimana cara bergabung dengan komunitas pelari di IndonesiaMarathon.com?',
                'answer' => 'Cara bergabung komunitas: 1) Klik menu "Ekosistem" → "Komunitas Lari". 2) Browse daftar komunitas di berbagai kota (Bandung, Jakarta, Yogyakarta, Surabaya, dll). 3) Klik komunitas yang menarik untuk lihat detail: lokasi, kontak, Instagram handle. 4) Hubungi komunitas langsung via WhatsApp atau Instagram yang tertera. 5) Untuk komunitas yang belum terdaftar di kami: hubungi support kami, kami bantu daftarkan. Alternatif: ikuti artikel blog kami tentang komunitas lari, ikuti media sosial kami (@indonesiamarathon_com) untuk update komunitas terbaru dan gathering info.',
                'sort_order' => 26,
                'is_active' => true,
                'related_keyword' => null,
            ],
            [
                'category' => 'website',
                'keyword' => null,
                'question' => 'Bagaimana untuk vendor/sponsor yang ingin listing di IndonesiaMarathon.com?',
                'answer' => 'Untuk vendor atau sponsor yang ingin listed: 1) Klik menu "Ekosistem" → pilih kategori (Vendor Medali, Race Management, atau daftar komunitas). 2) Lihat daftar yang sudah ada sebagai referensi. 3) Email ke partnership@indonesiamarathon.com dengan: nama bisnis, kategori, deskripsi, logo, kontak, website. 4) Ada dua opsi: Free listing (basic, data terbatas) atau Premium listing (featured, lebih visible, data lengkap). 5) Untuk premium: kami kirim rate card dan paket pricing. Benefit premium: featured di homepage, badge spesial, analytics clicks/views, prioritas dalam campaign. Tim kami akan follow up dalam 1-2 hari kerja.',
                'sort_order' => 27,
                'is_active' => true,
                'related_keyword' => null,
            ],
        ];

        foreach ($faqs as $faq) {
            FAQ::updateOrCreate(
                [
                    'question' => $faq['question'],
                ],
                $faq
            );
        }
    }
}