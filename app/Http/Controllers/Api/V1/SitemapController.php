<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Province;
use App\Models\StaticPage;
use App\Models\FAQ;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stephenjude\FilamentBlog\Models\Post;
use Stephenjude\FilamentBlog\Models\Category as BlogCategory;
use Spatie\Tags\Tag;

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
            $latestBlogUpdate = Post::whereNotNull('published_at')
                ->where('published_at', '<=', now())
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
            
            // Event submit page: use current time
            $pushUrl('/event/submit', 'pages', 'weekly', 0.6, now()->toAtomString());
            
            // Rate card: will be handled by StaticPage query below if exists

            // 2. Categories Sitemap
            EventCategory::query()
                ->select(['id', 'slug', 'updated_at'])
                ->whereHas('events', fn($q) => $q->where('status', 'published'))
                ->each(function (EventCategory $category) use ($pushUrl) {
                    $lastmod = optional($category->updated_at)->toAtomString();
                    $pushUrl("/event?category={$category->slug}", 'categories', 'daily', 0.8, $lastmod);
                });

            Province::query()
                ->select(['slug', 'updated_at'])
                ->whereHas('events', fn($q) => $q->where('status', 'published'))
                ->where('is_active', true)
                ->each(function (Province $province) use ($pushUrl) {
                    $lastmod = optional($province->updated_at)->toAtomString();
                    // Use direct URL construction instead of query params for SEO
                    $pushUrl("/event?province={$province->slug}", 'categories', 'daily', 0.8, $lastmod);
                });

            StaticPage::query()
                ->select(['slug', 'updated_at'])
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->each(function (StaticPage $page) use ($pushUrl) {
                    $lastmod = optional($page->updated_at)->toAtomString();
                    $pushUrl("/{$page->slug}", 'pages', 'monthly', 0.4, $lastmod);
                });

            FAQ::query()
                ->select('category')->whereNotNull('category')->groupBy('category')->orderBy('category')
                ->get()->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?category=" . urlencode($faq->category), 'categories', 'weekly', 0.6);
                });

            FAQ::query()
                ->select('keyword')->whereNotNull('keyword')->groupBy('keyword')->orderBy('keyword')
                ->get()->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?keyword=" . urlencode($faq->keyword), 'categories', 'weekly', 0.5);
                });

                try {
                    BlogCategory::query()
                        ->select(['slug', 'updated_at'])->isVisible()
                        ->each(function (BlogCategory $category) use ($pushUrl) {
                            $lastmod = optional($category->updated_at)->toAtomString();
                            $pushUrl("/blog?category={$category->slug}", 'categories', 'weekly', 0.6, $lastmod);
                        });
                } catch (\Exception $e) {
                    // Silently skip
                }

                try {
                    $tagIds = DB::table('taggables')->where('taggable_type', Post::class)->distinct()->pluck('tag_id')->toArray();
                    if (!empty($tagIds)) {
                        Tag::query()->whereIn('id', $tagIds)->get()->each(function (Tag $tag) use ($pushUrl) {
                            // Handle JSON slug column (Spatie Tags uses JSON for slug)
                            $slug = is_string($tag->slug) && !empty($tag->slug)
                                ? trim($tag->slug)
                                : (is_array($tag->slug) 
                                    ? ($tag->slug['en'] ?? ($tag->slug['id'] ?? reset($tag->slug)))
                                    : (is_object($tag->slug)
                                        ? ($tag->slug->en ?? ($tag->slug->id ?? null))
                                        : null));
                            
                            // Fallback: generate slug from name if slug is empty
                            if (empty($slug)) {
                                $name = is_string($tag->name) ? $tag->name : ($tag->name['en'] ?? ($tag->name['id'] ?? reset($tag->name)));
                                $slug = Str::slug($name ?? '');
                            }
                            
                            // Only add to sitemap if slug is valid
                            if (!empty($slug)) {
                                $pushUrl("/blog?tag=" . urlencode($slug), 'categories', 'weekly', 0.5,
                                    optional($tag->updated_at)->toAtomString());
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

                // 4. Blog Sitemap
                Post::query()
                    ->select(['slug', 'updated_at', 'published_at'])->whereNotNull('published_at')
                    ->where('published_at', '<=', now())->orderByDesc('published_at')
                    ->each(function (Post $post) use ($pushUrl) {
                        $lastmod = optional($post->updated_at ?? $post->published_at)->toAtomString();
                        $pushUrl("/blog/{$post->slug}", 'blog', 'weekly', 0.6, $lastmod);
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