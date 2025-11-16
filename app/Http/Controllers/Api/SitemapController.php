<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Province;
use App\Models\StaticPage;

use App\Models\FAQ;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Stephenjude\FilamentBlog\Models\Post;
use Stephenjude\FilamentBlog\Models\Category as BlogCategory;
use Spatie\Tags\Tag;

class SitemapController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $ttl = now()->addHours(6);

        $urls = Cache::remember('sitemap_urls', $ttl, function () {
            $baseUrl = rtrim(config('app.url'), '/');

            $items = collect();

            $pushUrl = function (string $path, string $changefreq = 'weekly', float $priority = 0.5, ?string $lastmod = null) use (&$items, $baseUrl) {
                $path = '/' . ltrim($path, '/');

                $items->push(array_filter([
                    'loc' => $baseUrl . $path,
                    'changefreq' => $changefreq,
                    'priority' => $priority,
                    'lastmod' => $lastmod,
                ]));
            };

            // Static pages
            $pushUrl('/', 'daily', 1.0);
            $pushUrl('/event', 'hourly', 0.9);
            $pushUrl('/blog', 'daily', 0.8);
            $pushUrl('/faq', 'weekly', 0.6);
            $pushUrl('/ekosistem', 'weekly', 0.7);
            $pushUrl('/ekosistem/komunitas-lari', 'weekly', 0.7);
            $pushUrl('/ekosistem/vendor-medali', 'weekly', 0.7);
            $pushUrl('/ekosistem/race-management', 'weekly', 0.7);
            $pushUrl('/ekosistem/vendor-jersey', 'weekly', 0.7);
            $pushUrl('/ekosistem/vendor-fotografer', 'weekly', 0.7);
            $pushUrl('/event/submit', 'weekly', 0.6);
            $pushUrl('/rate-card', 'monthly', 0.6);

            // Event type filter pages (for SEO filtering)
            EventCategory::query()
                ->select(['slug', 'updated_at'])
                ->where('is_active', true)
                ->each(function (EventCategory $category) use ($pushUrl) {
                    $lastmod = optional($category->updated_at)->toAtomString();
                    $pushUrl("/event?type={$category->slug}", 'daily', 0.8, $lastmod);
                });

            // Province filter pages (for SEO filtering)
            Province::query()
                ->select(['slug', 'updated_at'])
                ->where('is_active', true)
                ->each(function (Province $province) use ($pushUrl) {
                    $lastmod = optional($province->updated_at)->toAtomString();
                    $pushUrl("/event?province={$province->slug}", 'daily', 0.8, $lastmod);
                });

            // Static pages from database
            StaticPage::query()
                ->select(['slug', 'updated_at'])
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->each(function (StaticPage $page) use ($pushUrl) {
                    $lastmod = optional($page->updated_at)->toAtomString();
                    $pushUrl("/{$page->slug}", 'monthly', 0.4, $lastmod);
                });

            // FAQ category filter pages
            FAQ::query()
                ->select('category')
                ->whereNotNull('category')
                ->distinct()
                ->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?category=" . urlencode($faq->category), 'weekly', 0.6);
                });

            // FAQ keyword filter pages
            FAQ::query()
                ->select('keyword')
                ->whereNotNull('keyword')
                ->distinct()
                ->each(function (FAQ $faq) use ($pushUrl) {
                    $pushUrl("/faq?keyword=" . urlencode($faq->keyword), 'weekly', 0.5);
                });

            // Blog category filter pages
            BlogCategory::query()
                ->select(['slug', 'updated_at'])
                ->isVisible()
                ->each(function (BlogCategory $category) use ($pushUrl) {
                    $lastmod = optional($category->updated_at)->toAtomString();
                    $pushUrl("/blog?category={$category->slug}", 'weekly', 0.6, $lastmod);
                });

            // Blog tag filter pages
            Tag::query()
                ->whereHas('taggables', function ($query) {
                    $query->where('taggable_type', Post::class);
                })
                ->each(function (Tag $tag) use ($pushUrl) {
                    $pushUrl("/blog?tag=" . urlencode($tag->slug ?? $tag->name), 'weekly', 0.5, optional($tag->updated_at)->toAtomString());
                });

            // Published events
            Event::query()
                ->select(['slug', 'updated_at', 'status'])
                ->where('status', 'published')
                ->orderByDesc('updated_at')
                ->limit(1000)
                ->each(function (Event $event) use ($pushUrl) {
                    $lastmod = optional($event->updated_at)->toAtomString();
                    $pushUrl("/event/{$event->slug}", 'daily', 0.8, $lastmod);
                });

            // Published blog posts
            Post::query()
                ->select(['slug', 'updated_at', 'published_at'])
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->each(function (Post $post) use ($pushUrl) {
                    $lastmod = optional($post->updated_at ?? $post->published_at)->toAtomString();
                    $pushUrl("/blog/{$post->slug}", 'weekly', 0.6, $lastmod);
                });

            return $items->values()->all();
        });

        return response()->json($urls);
    }
}