<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VerifyR2Migration extends Command
{
    protected $signature = 'media:verify-r2-migration';
    protected $description = 'Verifikasi bahwa semua media sudah di-migrate ke R2 dan aman untuk menghapus folder public';

    public function handle(): int
    {
        $this->info('Verifying R2 migration status...');
        $this->newLine();

        // 1. Cek media yang masih di public disk
        $publicMediaCount = Media::where('disk', 'public')->count();
        $r2MediaCount = Media::where('disk', 'r2')->count();
        $totalMedia = Media::count();

        $this->line("Total Media Records: {$totalMedia}");
        $this->line("Media di R2: {$r2MediaCount}");
        $this->line("Media masih di Public: {$publicMediaCount}");
        $this->newLine();

        if ($publicMediaCount > 0) {
            $this->error("⚠️  Masih ada {$publicMediaCount} media yang belum di-migrate ke R2!");
            $this->warn("Jalankan: php artisan media:migrate-to-r2");
            $this->newLine();

            return self::FAILURE;
        }

        $this->info("✓ Semua media sudah di R2 ({$r2MediaCount} records)");
        $this->newLine();

        // 2. Cek file di storage/app/public
        $publicFiles = Storage::disk('public')->allFiles();
        $mediaFiles = array_filter($publicFiles, function ($file) {
            // Hanya file media (numeric folder ID dari Spatie)
            return preg_match('/^\d+\//', $file);
        });

        $mediaFilesCount = count($mediaFiles);

        if ($mediaFilesCount > 0) {
            $this->warn("⚠️  Masih ada {$mediaFilesCount} file media di storage/app/public");
            $this->line("File-file ini bisa dihapus setelah verifikasi manual:");
            $this->newLine();

            // Group by directory
            $directories = [];
            foreach ($mediaFiles as $file) {
                $dir = dirname($file);
                if (! isset($directories[$dir])) {
                    $directories[$dir] = 0;
                }
                $directories[$dir]++;
            }

            foreach (array_slice($directories, 0, 10) as $dir => $count) {
                $this->line("  - {$dir}/ ({$count} files)");
            }

            if (count($directories) > 10) {
                $this->line("  ... dan " . (count($directories) - 10) . " directory lainnya");
            }

            $this->newLine();
            $this->comment("💡 File-file ini adalah sisa dari migrasi dan aman untuk dihapus.");
            $this->comment("   Tapi pastikan dulu bahwa semua media sudah terlihat di frontend dari R2.");
        } else {
            $this->info("✓ Tidak ada file media tersisa di storage/app/public");
        }

        $this->newLine();
        $this->info("📋 Checklist sebelum menghapus:");
        $this->line("  1. ✓ Semua media sudah di R2 (verified)");
        $this->line("  2. ⬜ Test akses gambar di frontend (pastikan semua gambar tampil dari R2)");
        $this->line("  3. ⬜ Test upload media baru (pastikan tersimpan ke R2)");
        $this->line("  4. ⬜ Backup folder storage/app/public (opsional, untuk safety)");
        $this->newLine();

        if ($mediaFilesCount > 0) {
            $this->comment("Setelah semua checklist selesai, jalankan:");
            $this->line("  rm -rf storage/app/public/[0-9]*");
            $this->line("  (Hanya hapus folder numeric, jangan hapus folder lain seperti 'images', dll)");
        } else {
            $this->info("✅ Semua sudah bersih! Folder public media sudah kosong.");
        }

        return self::SUCCESS;
    }
}