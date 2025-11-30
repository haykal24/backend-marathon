<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Province;
use App\Models\StaticPage;
use App\Models\FAQ;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\RunningCommunity;
use App\Models\Vendor;

class SitemapController extends BaseApiController
{
    /**
     * Handle the incoming request.
     * 
     * Generate sitemap data for Nuxt SEO module.
     * Format: Array of objects with loc, changefreq, priority, lastmod
     * 
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        try {
        // Cache for 6 hours (sitemap tidak perlu realtime)
        $ttl = now()->addHours(6);

        $urls = Cache::remember('sitemap_urls', $ttl, function () {
            // Use frontend URL (FRONTEND_URL or NUXT_PUBLIC_SITE_URL) instead of backend URL
            // This ensures sitemap has correct frontend URLs for SEO
            // Priority: FRONTEND_URL > NUXT_PUBLIC_SITE_URL > default localhost:3000
            $baseUrl = rtrim(
                env('FRONTEND_URL', env('NUXT_PUBLIC_SITE_URL', 'http://localhost:3000')),
                '/'
            );
            
            // Validate baseUrl is frontend (not backend)
            // Backend usually has port 8000, frontend usually 3000 or no port
            if (str_contains($baseUrl, ':8000')) {
                // Fallback to localhost:3000 if backend URL detected
                $baseUrl = 'http://localhost:3000';
            }

            $items = collect();

            // Add _sitemap parameter back
            $pushUrl = function (string $path, string $_sitemap, string $changefreq = 'weekly', float $priority = 0.5, ?string $lastmod = null) use (&$items,
                $baseUrl) {
                $path = '/' . ltrim($path, '/');

                $item = [
                    'loc' => $baseUrl . $path,
                    '_sitemap' => $_sitemap, // This is the crucial key for chunking
                    'changefreq' => $changefreq,
                    'priority' => $priority,
                ];
                
                // Only add lastmod if it's not null
                if ($lastmod !== null) {
                    $item['lastmod'] = $lastmod;
                }
                
                $items->push($item);
            };

            // --- URL Categorization ---

            // Get latest update timestamps for dynamic pages
            $latestEventUpdate = Event::where('status', 'published')
                ->max('updated_at');
            $latestBlogUpdate = BlogPost::visibleOnIndonesiaMarathon()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where('status', 'published')
                ->max('updated_at');
            $latestUpdate = $latestEventUpdate && $latestBlogUpdate
                ? max($latestEventUpdate, $latestBlogUpdate)
                : ($latestEventUpdate ?? $latestBlogUpdate ?? now());

            // 1. Pages Sitemap
            // Homepage: use latest event/blog update (most dynamic)
            $pushUrl('/', 'pages', 'daily', 1.0, optional($latestUpdate)->toAtomString() ?? now()->toAtomString());
            
            // Event listing: use latest event update
            $pushUrl('/event', 'pages', 'hourly', 0.9, optional($latestEventUpdate)->toAtomString() ?? now()->toAtomString());
            
            // Blog listing: use latest blog update
            $pushUrl('/blog', 'pages', 'daily', 0.8, optional($latestBlogUpdate)->toAtomString() ?? now()->toAtomString());
            
            // FAQ: use current time (static but can be updated)
            $pushUrl('/faq', 'pages', 'weekly', 0.6, now()->toAtomString());
            
            // Ekosistem pages: use current time
            $pushUrl('/ekosistem', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/ekosistem/komunitas-lari', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/ekosistem/vendor-medali', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/ekosistem/race-management', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/ekosistem/vendor-jersey', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/ekosistem/vendor-fotografer', 'pages', 'weekly', 0.7, now()->toAtomString());

            // Ecosystem Detail Pages (Vendors & Communities)
            try {
                // Running Communities
                RunningCommunity::query()
                    ->select(['slug', 'updated_at'])
                    ->whereNotNull('slug')
                    ->each(function (RunningCommunity $community) use ($pushUrl) {
                        $lastmod = optional($community->updated_at)->toAtomString();
                        $pushUrl("/ekosistem/komunitas-lari/{$community->slug}", 'categories', 'weekly', 0.6, $lastmod);
                    });

                // Vendors
                Vendor::query()
                    ->select(['slug', 'updated_at'])
                    ->whereNotNull('slug')
                    ->each(function (Vendor $vendor) use ($pushUrl) {
                        $lastmod = optional($vendor->updated_at)->toAtomString();
                        $pushUrl("/ekosistem/vendor/{$vendor->slug}", 'categories', 'weekly', 0.6, $lastmod);
                    });
            } catch (\Exception $e) {
                // Silently skip
            }
            
            // Event submit page: use current time
            $pushUrl('/event/submit', 'pages', 'weekly', 0.6, now()->toAtomString());
            
            // Programmatic SEO Pages (index pages - untuk breadcrumb & sitemap)
            $pushUrl('/event/provinsi', 'pages', 'weekly', 0.8, now()->toAtomString());
            $pushUrl('/event/kategori', 'pages', 'weekly', 0.8, now()->toAtomString());
            $pushUrl('/event/kota', 'pages', 'weekly', 0.7, now()->toAtomString());
            $pushUrl('/event/bulan', 'pages', 'weekly', 0.7, now()->toAtomString());
            
            // Blog Programmatic SEO Pages (index pages)
            $pushUrl('/blog/tag', 'pages', 'weekly', 0.7, optional($latestBlogUpdate)->toAtomString() ?? now()->toAtomString());
            $pushUrl('/blog/kategori', 'pages', 'weekly', 0.7, optional($latestBlogUpdate)->toAtomString() ?? now()->toAtomString());
            
            // Cities (Kota/Kabupaten) - Dynamic Routes
            try {
                $cities = DB::table('events')
                    ->select('city', 'province', DB::raw('COUNT(*) as event_count'))
                    ->where('status', 'published')
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->groupBy('city', 'province')
                    ->having('event_count', '>', 0)
                    ->orderByDesc('event_count')
                    ->limit(100) // Limit top 100 kota untuk crawl budget
                    ->get();

                foreach ($cities as $city) {
                    $citySlug = Str::slug($city->city);
                    $pushUrl("/event/kota/{$citySlug}", 'categories', 'daily', 0.8, now()->toAtomString());
                }
            } catch (\Exception $e) {
                // Silently skip
            }

            // Months - Dynamic Routes (12 bulan dari sekarang ke depan)
            for ($i = 0; $i < 12; $i++) {
                $date = now()->addMonths($i);
                $monthSlug = $date->format('Y-m'); // 2025-01
                $pushUrl("/event/bulan/{$monthSlug}", 'categories', 'weekly', 0.7, now()->toAtomString());
            }
            
            // Rate card: will be handled by StaticPage query below if exists

            // 2. Categories / Facet Sitemap
            // Event Types - Dynamic Routes (Programmatic SEO)
            EventType::query()
                ->select(['id', 'slug', 'name', 'updated_at'])
                ->where('is_active', true)
                ->whereHas('events', fn($q) => $q->where('status', 'published'))
                ->each(function (EventType $type) use ($pushUrl) {
                    $lastmod = optional($type->updated_at)->toAtomString();
                    // Dynamic route untuk SEO yang lebih kuat
                    $pushUrl("/event/kategori/{$type->slug}", 'categories', 'daily', 0.9, $lastmod);
                });

            // Provinces - Dynamic Routes (Programmatic SEO)
            Province::query()
                ->select(['slug', 'updated_at'])
                ->whereHas('events', fn($q) => $q->where('status', 'published'))
                ->where('is_active', true)
                ->each(function (Province $province) use ($pushUrl) {
                    $lastmod = optional($province->updated_at)->toAtomString();
                    // Dynamic route untuk SEO yang lebih kuat
                    $pushUrl("/event/provinsi/{$province->slug}", 'categories', 'daily', 0.9, $lastmod);
                });

            StaticPage::query()
                ->select(['slug', 'updated_at'])
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->each(function (StaticPage $page) use ($pushUrl) {
                    $lastmod = optional($page->updated_at)->toAtomString();
                    $pushUrl("/{$page->slug}", 'pages', 'monthly', 0.4, $lastmod);
                });

            // FAQ Categories
            FAQ::query()
                ->select('category')->whereNotNull('category')->groupBy('category')->orderBy('category')
                ->get()->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?category=" . urlencode($faq->category), 'categories', 'weekly', 0.6);
                });

            // FAQ Keywords
            FAQ::query()
                ->select('keyword')->whereNotNull('keyword')->groupBy('keyword')->orderBy('keyword')
                ->get()->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?keyword=" . urlencode($faq->keyword), 'categories', 'weekly', 0.5);
                });

            // Blog Categories - Dynamic Routes (Programmatic SEO)
            try {
                BlogCategory::query()
                    ->select(['slug', 'updated_at'])
                    ->where('is_visible', true)
                    ->each(function (BlogCategory $category) use ($pushUrl) {
                        $lastmod = optional($category->updated_at)->toAtomString();
                        // Dynamic route untuk SEO yang lebih kuat
                        $pushUrl("/blog/kategori/{$category->slug}", 'categories', 'daily', 0.8, $lastmod);
                    });
            } catch (\Exception $e) {
                // Silently skip
            }

            // Blog Tags - Dynamic Routes (Programmatic SEO - hanya untuk indonesiamarathon.com)
            try {
                // Get tag IDs dari blog posts yang visible di indonesiamarathon.com
                $blogPostIds = BlogPost::visibleOnIndonesiaMarathon()
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->pluck('id')
                    ->toArray();
                
                if (empty($blogPostIds)) {
                    $tagIds = [];
                } else {
                    $tagIds = DB::table('taggables')
                        ->where('taggable_type', BlogPost::class)
                        ->whereIn('taggable_id', $blogPostIds)
                        ->distinct()
                        ->pluck('tag_id')
                        ->toArray();
                }
                
                if (!empty($tagIds)) {
                    DB::table('tags')
                        ->whereIn('id', $tagIds)
                        ->get()
                        ->each(function ($tag) use ($pushUrl) {
                            // Parse slug (handle JSON if needed)
                            $slug = is_string($tag->slug) && !empty($tag->slug)
                                ? trim($tag->slug)
                                : null;
                            
                            if (!$slug && isset($tag->name)) {
                                $name = is_string($tag->name) ? $tag->name : (is_array($tag->name) ? ($tag->name['id'] ?? $tag->name['en'] ?? reset($tag->name)) : null);
                                if ($name) {
                                    $slug = Str::slug($name);
                                }
                            }
                            
                            if (!empty($slug)) {
                                // Dynamic route untuk SEO yang lebih kuat
                                $pushUrl("/blog/tag/{$slug}", 'categories', 'daily', 0.7, null);
                            }
                        });
                }
            } catch (\Exception $e) {
                // Silently skip
            }

                // 3. Events Sitemap
                Event::query()
                    ->select(['slug', 'updated_at', 'status'])->where('status', 'published')
                    ->orderByDesc('updated_at')->limit(1000)
                    ->each(function (Event $event) use ($pushUrl) {
                        $lastmod = optional($event->updated_at)->toAtomString();
                        $pushUrl("/event/{$event->slug}", 'events', 'daily', 0.8, $lastmod);
                    });

                // 4. Blog Sitemap (Custom Blog System - hanya untuk indonesiamarathon.com)
                BlogPost::query()
                    ->visibleOnIndonesiaMarathon() // Filter untuk indonesiamarathon.com
                    ->select(['slug', 'updated_at', 'published_at'])
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->each(function (BlogPost $post) use ($pushUrl) {
                        $lastmod = optional($post->updated_at ?? $post->published_at)->toAtomString();
                        $pushUrl("/blog/{$post->slug}", 'blog', 'weekly', 0.7, $lastmod);
                    });

                return $items->values()->all();
            });

            return response()->json($urls);
        } catch (\Exception $e) {
            \Log::error('Sitemap generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to generate sitemap',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}