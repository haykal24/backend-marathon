<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\BlogPostResource;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;
use Illuminate\Support\Str;

class BlogController extends BaseApiController
{
    /**
     * Get list of published blog posts
     */
    public function posts(Request $request): JsonResponse
    {
        // Gunakan App\Models\BlogPost untuk akses scope visibleOnIndonesiaMarathon
        // Filter hanya artikel untuk indonesiamarathon.com (is_for_maraton_id = false atau null)
        $query = BlogPost::with([
            'author',
            'category',
            'tags' => function ($query) {
                // Only load tags with valid name and slug
                $query->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->select('id', 'name', 'slug');
            }
        ])
            ->visibleOnIndonesiaMarathon() // Filter untuk indonesiamarathon.com
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('status', 'published'); // Tambahkan filter status published

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by tag
        if ($request->has('tag')) {
            $tagSlug = $request->tag;
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                // Spatie Tags uses JSON column for slug, so we need to query JSON
                // Try both: direct JSON query and fallback to name matching
                $q->where(function ($query) use ($tagSlug) {
                    // Query JSON column: slug->'en' or slug->'id' or any locale
                    $query->whereJsonContains('slug', $tagSlug)
                        ->orWhere(function ($q) use ($tagSlug) {
                            // Fallback: check all possible JSON paths
                            $locales = ['en', 'id'];
                            foreach ($locales as $locale) {
                                $q->orWhere("slug->{$locale}", $tagSlug);
                            }
                        });
                });
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Sort
        $query->orderBy('published_at', 'desc');

        // Pagination
        $perPage = min($request->get('per_page', 10), 50); // Max 50 per page
        $posts = $query->paginate($perPage);

        return $this->paginatedResponse(
            $posts,
            BlogPostResource::collection($posts)
        );
    }

    /**
     * Get single blog post by slug
     */
    public function show(string $slug): JsonResponse
    {
        // Cek dulu apakah post dengan slug ini ada (tanpa filter portal/status)
        $postExists = BlogPost::where('slug', $slug)->exists();
        
        if (!$postExists) {
            return $this->errorResponse('Blog post tidak ditemukan.', 404);
        }

        // Gunakan App\Models\BlogPost untuk akses scope visibleOnIndonesiaMarathon
        $post = BlogPost::with([
            'author',
            'category',
            'tags' => function ($query) {
                // Only load tags with valid name and slug
                $query->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->select('id', 'name', 'slug');
            }
        ])
            ->visibleOnIndonesiaMarathon() // Filter untuk indonesiamarathon.com
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('status', 'published') // Tambahkan filter status published
            ->first();

        if (!$post) {
            // Post ada tapi tidak memenuhi kriteria (portal, status, atau published_at)
            return $this->errorResponse('Blog post tidak tersedia atau belum dipublikasikan.', 404);
        }

        return $this->successResponse(
            new BlogPostResource($post)
        );
    }

    /**
     * Get list of blog categories
     */
    public function categories(): JsonResponse
    {
        // Gunakan App\Models\BlogCategory (custom model, bukan dari plugin)
        $categories = BlogCategory::where('is_visible', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ];
            })
        );
    }

    /**
     * Get list of blog tags with post counts
     */
    public function tags(): JsonResponse
    {
        // Get all tags that are used by published posts untuk indonesiamarathon.com
        $tags = Tag::whereHas('posts', function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($q) {
                    // Filter untuk indonesiamarathon.com (is_for_maraton_id = false atau null)
                    $q->whereNull('is_for_maraton_id')
                      ->orWhere('is_for_maraton_id', false);
                });
        })
            ->get()
            ->map(function ($tag) {
                // Handle JSON name and slug from Spatie Tags
                $name = is_string($tag->name) 
                    ? $tag->name 
                    : (is_array($tag->name) ? ($tag->name['id'] ?? $tag->name['en'] ?? reset($tag->name)) : '');
                
                $slug = is_string($tag->slug) && !empty($tag->slug)
                    ? $tag->slug
                    : (is_array($tag->slug) 
                        ? ($tag->slug['id'] ?? $tag->slug['en'] ?? reset($tag->slug)) 
                        : Str::slug($name));

                // Count posts with this tag untuk indonesiamarathon.com
                $postCount = BlogPost::where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->visibleOnIndonesiaMarathon() // Filter untuk indonesiamarathon.com
                    ->whereHas('tags', function ($q) use ($tag) {
                        $q->where('tags.id', $tag->id);
                    })
                    ->count();

                return [
                    'id' => $tag->id,
                    'name' => trim($name),
                    'slug' => $slug ?: Str::slug($name),
                    'post_count' => $postCount,
                ];
            })
            ->filter(function ($tag) {
                // Filter out tags with empty name or slug
                return !empty($tag['name']) && !empty($tag['slug']);
            })
            ->sortByDesc('post_count')
            ->values();

        return $this->successResponse($tags);
    }
}