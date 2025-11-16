<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReorganizeEventImages extends Command
{
    protected $signature = 'events:reorganize-images 
                            {--delete-original : Hapus file original setelah konversi WebP}
                            {--force-convert : Paksa konversi ulang ke WebP}';

    protected $description = 'Reorganize event images: convert to WebP, delete originals, and optimize storage structure';

    public function handle()
    {
        $deleteOriginal = $this->option('delete-original');
        $forceConvert = $this->option('force-convert');

        $this->info('Reorganizing event images...');
        if ($deleteOriginal) {
            $this->warn('⚠️  Original files will be deleted after WebP conversion!');
        }

        $events = Event::has('media')->get();
        $this->info("Found {$events->count()} events with images");

        $processed = 0;
        $deleted = 0;
        $errors = 0;

        $this->withProgressBar($events, function ($event) use ($deleteOriginal, $forceConvert, &$processed, &$deleted, &$errors) {
            try {
                $media = $event->getFirstMedia('default');
                if (!$media) {
                    return;
                }

                // Cek apakah WebP conversion sudah ada
                $hasWebp = $media->hasGeneratedConversion('webp');
                
                // Hapus file original setelah konversi WebP (jika WebP sudah ada)
                if ($deleteOriginal && $hasWebp) {
                    $originalPath = $media->getPath();
                    $webpPath = $media->getPath('webp');

                    // Hanya hapus jika original bukan WebP dan WebP file ada
                    if (File::exists($originalPath) && $originalPath !== $webpPath) {
                        $ext = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['webp'])) {
                            // Pastikan WebP file ada sebelum hapus original
                            if (File::exists($webpPath)) {
                                File::delete($originalPath);
                                $deleted++;
                            }
                        }
                    }
                } elseif (!$hasWebp && $forceConvert) {
                    // Jika WebP belum ada, coba regenerate (hanya log warning)
                    $this->warn("WebP conversion not found for event {$event->id}. Run: php artisan media-library:regenerate");
                }

                $processed++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing event {$event->id}: " . $e->getMessage());
            }
        });

        $this->newLine(2);
        $this->info("Summary:");
        $this->info("  ✓ Processed: {$processed} events");
        if ($deleteOriginal) {
            $this->info("  🗑️  Original files deleted: {$deleted}");
        }
        if ($errors > 0) {
            $this->error("  ✗ Errors: {$errors}");
        }

        return Command::SUCCESS;
    }
}