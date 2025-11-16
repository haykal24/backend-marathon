<?php

use App\Http\Controllers\Api\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/__sitemap__/urls', SitemapController::class);

Route::prefix('v1')->group(function () {
    // Ad Banners
    Route::get('/banners', [App\Http\Controllers\Api\V1\AdBannerController::class, 'index']);

    // Events
    Route::get('/events/featured-hero', [App\Http\Controllers\Api\V1\EventController::class, 'getFeaturedHeroEvents']);
    Route::get('/events/calendar-stats', [App\Http\Controllers\Api\V1\EventController::class, 'getCalendarStats']);
    Route::get('/events/slug/{slug}', [App\Http\Controllers\Api\V1\EventController::class, 'showBySlug']);
    Route::get('/events/search', [App\Http\Controllers\Api\V1\EventController::class, 'search']);
    Route::apiResource('/events', App\Http\Controllers\Api\V1\EventController::class)->except(['update', 'destroy']);
    
    Route::get('/event-types', [App\Http\Controllers\Api\V1\EventTypeController::class, 'index']);
    Route::get('/event-types/{slug}', [App\Http\Controllers\Api\V1\EventTypeController::class, 'show']);
    
    Route::get('/provinces', [App\Http\Controllers\Api\V1\ProvinceController::class, 'index']);
    Route::get('/provinces/{slug}', [App\Http\Controllers\Api\V1\ProvinceController::class, 'show']);
    
    Route::get('/site-settings', [App\Http\Controllers\Api\V1\SiteSettingController::class, 'index']);
    Route::get('/site-settings/{key}', [App\Http\Controllers\Api\V1\SiteSettingController::class, 'show']);
    
    Route::get('/blog/posts', [App\Http\Controllers\Api\V1\BlogController::class, 'posts']);
    Route::get('/blog/posts/{slug}', [App\Http\Controllers\Api\V1\BlogController::class, 'show']);
    Route::get('/blog/categories', [App\Http\Controllers\Api\V1\BlogController::class, 'categories']);
    
    Route::get('/vendors', [App\Http\Controllers\Api\V1\DirectoryController::class, 'vendors']);
    Route::get('/running-communities', [App\Http\Controllers\Api\V1\DirectoryController::class, 'runningCommunities']);
    
    // Static pages
    Route::get('/pages', [App\Http\Controllers\Api\V1\PageController::class, 'index']);
    Route::get('/pages/{slug}', [App\Http\Controllers\Api\V1\PageController::class, 'show']);
    
    Route::get('/ad-banners', [App\Http\Controllers\Api\V1\AdBannerController::class, 'index']);
    Route::get('/rate-packages', [App\Http\Controllers\Api\V1\RatePackageController::class, 'index']);
    Route::get('/rate-packages/{slug}', [App\Http\Controllers\Api\V1\RatePackageController::class, 'show']);
    
    // FAQ routes
    Route::get('/faqs', [App\Http\Controllers\Api\V1\FAQController::class, 'index']);
    Route::get('/faqs/categories', [App\Http\Controllers\Api\V1\FAQController::class, 'getCategories']);
    Route::get('/faqs/category/{category}', [App\Http\Controllers\Api\V1\FAQController::class, 'getByCategory']);
    Route::get('/faqs/{id}', [App\Http\Controllers\Api\V1\FAQController::class, 'show']);
    
    // OTP routes (public)
    Route::post('/otp/request', [App\Http\Controllers\Api\V1\OtpController::class, 'request']);
    Route::post('/otp/verify', [App\Http\Controllers\Api\V1\OtpController::class, 'verify']);
    
    // Authenticated routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/events/submit', [App\Http\Controllers\Api\V1\EventController::class, 'store']);
        Route::get('/user', [App\Http\Controllers\Api\V1\AuthController::class, 'user']);
        Route::post('/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
    });
});
