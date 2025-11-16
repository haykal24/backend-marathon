<?php

namespace App\Console\Commands;

use App\Models\AdBanner;
use App\Models\RunningCommunity;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixMissingMedia extends Command
{
    protected $signature = 'media:fix-missing {--force : Force re-upload even if media exists}';
    protected $description = 'Fix missing media files for seeded models (Vendors, AdBanners, RunningCommunities)';

    public function handle(): int
    {
        $force = $this->option('force');
        $placeholderPath = public_path('seeders/placeholder.jpg');
        
        if (!File::exists($placeholderPath)) {
            $this->warn("Placeholder image not found at: {$placeholderPath}");
            $this->warn("Skipping media upload. Models without media will use fallback URL.");
            $this->newLine();
            
            // Still show statistics even without placeholder
            $this->showStatistics($force);
            return Command::SUCCESS;
        }

        $this->info('Fixing missing media files...');
        $this->newLine();

        // Fix Vendors
        $this->info('Processing Vendors...');
        $vendors = Vendor::all();
        $vendorCount = 0;
        foreach ($vendors as $vendor) {
            if ($force || $vendor->getMedia('default')->isEmpty()) {
                // Copy to temp location for each vendor to preserve original
                $tempPath = storage_path('app/temp_placeholder_' . $vendor->id . '_' . time() . '_' . uniqid() . '.jpg');
                File::copy($placeholderPath, $tempPath);

                try {
                    $vendor->clearMediaCollection('default');
                    $vendor->addMedia($tempPath)
                        ->usingName('Logo ' . $vendor->name)
                        ->toMediaCollection('default');
                    $vendorCount++;
                } finally {
                    // Clean up temp file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                }
            }
        }
        $this->line("  ✓ Fixed {$vendorCount} vendors");

        // Fix AdBanners
        $this->info('Processing AdBanners...');
        $banners = AdBanner::all();
        $bannerCount = 0;
        foreach ($banners as $banner) {
            if ($force || $banner->getMedia('default')->isEmpty()) {
                // Copy to temp location for each banner to preserve original
                $tempPath = storage_path('app/temp_placeholder_' . $banner->id . '_' . time() . '_' . uniqid() . '.jpg');
                File::copy($placeholderPath, $tempPath);

                try {
                    $banner->clearMediaCollection('default');
                    $banner->addMedia($tempPath)
                        ->usingName($banner->name)
                        ->toMediaCollection('default');
                    $bannerCount++;
                } finally {
                    // Clean up temp file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                }
            }
        }
        $this->line("  ✓ Fixed {$bannerCount} ad banners");

        // Fix RunningCommunities
        $this->info('Processing RunningCommunities...');
        $communities = RunningCommunity::all();
        $communityCount = 0;
        foreach ($communities as $community) {
            if ($force || $community->getMedia('default')->isEmpty()) {
                // Copy to temp location for each community to preserve original
                $tempPath = storage_path('app/temp_placeholder_' . $community->id . '_' . time() . '_' . uniqid() . '.jpg');
                File::copy($placeholderPath, $tempPath);

                try {
                    $community->clearMediaCollection('default');
                    $community->addMedia($tempPath)
                        ->usingName('Logo ' . $community->name)
                        ->toMediaCollection('default');
                    $communityCount++;
                } finally {
                    // Clean up temp file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                }
            }
        }
        $this->line("  ✓ Fixed {$communityCount} running communities");

        $this->newLine();
        $this->info("Successfully fixed media for {$vendorCount} vendors, {$bannerCount} banners, and {$communityCount} communities!");

        return Command::SUCCESS;
    }

    protected function showStatistics(bool $force): void
    {
        $vendorsWithoutMedia = Vendor::all()->filter(fn($v) => $v->getMedia('default')->isEmpty())->count();
        $bannersWithoutMedia = AdBanner::all()->filter(fn($b) => $b->getMedia('default')->isEmpty())->count();
        $communitiesWithoutMedia = RunningCommunity::all()->filter(fn($c) => $c->getMedia('default')->isEmpty())->count();

        $this->info('Media Statistics:');
        $this->line("  Vendors without media: {$vendorsWithoutMedia}");
        $this->line("  AdBanners without media: {$bannersWithoutMedia}");
        $this->line("  RunningCommunities without media: {$communitiesWithoutMedia}");
        $this->newLine();
        $this->info("To fix missing media, create a placeholder image at: " . public_path('seeders/placeholder.jpg'));
    }
}

