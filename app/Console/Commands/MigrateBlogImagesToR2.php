<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Stephenjude\FilamentBlog\Models\Post;
use Throwable;

class MigrateBlogImagesToR2 extends Command
{
    protected $signature = 'blog:migrate-images-to-r2
                            {--dry-run : Only report files that would be uploaded without making actual changes}';

    protected $description = 'Migrate blog post banner images from public storage to Cloudflare R2';

    public function handle(): int
    {
        if (! config('filesystems.disks.r2')) {
            $this->error('Disk [r2] is not configured. Please update config/filesystems.php first.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info('Migrating blog post banner images to R2...' . ($dryRun ? ' (dry run)' : ''));
        $this->newLine();

        // Get all posts with banner
        $posts = Post::whereNotNull('banner')
            ->where('banner', '!=', '')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No blog posts with banner images found.');

            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} blog posts with banner images.");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($posts as $post) {
            $this->line("Processing Post ID {$post->id}: {$post->title}");

            $bannerPath = $post->banner;

            // Skip if already a full URL (already in R2 or external)
            if (filter_var($bannerPath, FILTER_VALIDATE_URL)) {
                $this->comment("  → Skipped: Already a URL (external or R2)");
                $skippedCount++;
                continue;
            }

            // Check if file exists in public storage
            $publicPath = storage_path('app/public/' . $bannerPath);
            if (! file_exists($publicPath)) {
                $this->warn("  × File not found: {$bannerPath}");
                $errorCount++;
                continue;
            }

            if ($dryRun) {
                $this->comment("  • Would upload: {$bannerPath}");
                $successCount++;
                continue;
            }

            try {
                // Read file from public storage
                $fileContent = file_get_contents($publicPath);
                if ($fileContent === false) {
                    throw new \Exception("Failed to read file: {$publicPath}");
                }

                // Upload to R2
                Storage::disk('r2')->put($bannerPath, $fileContent, 'public');

                // Get R2 public URL from config
                $r2PublicUrl = config('filesystems.disks.r2.url');
                if (! $r2PublicUrl) {
                    throw new \Exception('R2_PUBLIC_URL is not configured in .env');
                }
                $r2Url = rtrim($r2PublicUrl, '/') . '/' . ltrim($bannerPath, '/');

                // Update post banner to R2 URL
                $post->update(['banner' => $r2Url]);

                $this->info("  ✓ Uploaded and updated: {$bannerPath} → {$r2Url}");
                $successCount++;
            } catch (Throwable $exception) {
                $this->error("  × Error: {$exception->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info('Migration Summary:');
        $this->line("  ✓ Success: {$successCount}");
        $this->line("  ⚠ Skipped: {$skippedCount}");
        $this->line("  × Errors: {$errorCount}");

        if (! $dryRun && $successCount > 0) {
            $this->newLine();
            $this->comment('💡 After verifying all images work correctly, you can delete the blog images from public storage:');
            $this->line('   rm -rf storage/app/public/blog');
        }

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}

