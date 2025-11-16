<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

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
    }
}