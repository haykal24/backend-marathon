<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure storage/app/public directory exists for Spatie Media Library
        $publicStoragePath = storage_path('app/public');
        if (!File::exists($publicStoragePath)) {
            File::makeDirectory($publicStoragePath, 0755, true);
        }

        // Load custom translations for filament-blog from lang/id/ (not vendor)
        // This ensures translations persist even after vendor reinstall
        // For namespace filament-blog::filament-blog.*, Laravel looks in lang/vendor/filament-blog/{locale}/filament-blog.php
        $customTranslationPath = lang_path('id/filament-blog.php');
        $vendorTranslationPath = lang_path('vendor/filament-blog/id/filament-blog.php');
        
        if (File::exists($customTranslationPath) && !File::exists($vendorTranslationPath)) {
            // Create vendor directory structure if needed
            $vendorDir = lang_path('vendor/filament-blog/id');
            if (!File::exists($vendorDir)) {
                File::makeDirectory($vendorDir, 0755, true);
            }
            
            // Copy custom translation to vendor path (Laravel will load from here)
            File::copy($customTranslationPath, $vendorTranslationPath);
        }
    }
}