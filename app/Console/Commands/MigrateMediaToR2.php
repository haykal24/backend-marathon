<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class MigrateMediaToR2 extends Command
{
    protected $signature = 'media:migrate-to-r2
                            {--chunk=50 : Number of media records processed per batch}
                            {--dry-run : Only report files that would be uploaded without moving anything}';

    protected $description = 'Move existing Spatie Media Library files from the public disk to Cloudflare R2.';

    public function handle(): int
    {
        if (! config('filesystems.disks.r2')) {
            $this->error('Disk [r2] is not configured. Please update config/filesystems.php first.');

            return self::FAILURE;
        }

        $chunk = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            'Migrating media stored on disk [public] to [r2] (chunk size: %d)%s',
            $chunk,
            $dryRun ? ' — dry run' : ''
        ));

        Media::where('disk', 'public')
            ->orderBy('id')
            ->chunkById($chunk, function ($mediaItems) use ($dryRun) {
                foreach ($mediaItems as $media) {
                    $this->migrateMedia($media, $dryRun);
                }
            });

        $this->info('Migration complete.');

        return self::SUCCESS;
    }

    protected function migrateMedia(Media $media, bool $dryRun = false): void
    {
        $directory = (string) $media->getKey();

        if (! Storage::disk('public')->directoryExists($directory)) {
            $this->warn("Directory {$directory} does not exist on [public], skipping media ID {$media->id}.");

            return;
        }

        $files = Storage::disk('public')->allFiles($directory);

        if (empty($files)) {
            $this->warn("Media ID {$media->id} has no files on [public], skipping.");

            return;
        }

        $this->line("Processing media ID {$media->id} ({$media->file_name})");

        foreach ($files as $file) {
            if ($dryRun) {
                $this->comment("  • would upload {$file}");

                continue;
            }

            try {
                $stream = Storage::disk('public')->readStream($file);

                if ($stream === false) {
                    $this->error("  × failed to read {$file}, skipping.");

                    continue;
                }

                Storage::disk('r2')->put($file, $stream, [
                    'visibility' => 'public',
                ]);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $this->comment("  ✓ uploaded {$file}");
            } catch (Throwable $exception) {
                $this->error("  × error while uploading {$file}: {$exception->getMessage()}");
            }
        }

        if (! $dryRun) {
            $media->forceFill([
                'disk' => 'r2',
                'conversions_disk' => 'r2',
            ])->save();

            $this->info("  → Media record {$media->id} updated to disk [r2].");
        }
    }
}