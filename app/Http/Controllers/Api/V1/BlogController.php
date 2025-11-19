<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\BlogPostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stephenjude\FilamentBlog\Models\Post;
use Stephenjude\FilamentBlog\Models\Category;

class BlogController extends BaseApiController
{
    /**
     * Get list of published blog posts
     */
    public function posts(Request $request): JsonResponse
    {
        $query = Post::with([
            'author',
            'category',
            'tags' => function ($query) {
                // Only load tags with valid name and slug
                $query->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->select('id', 'name', 'slug');
            }
        ])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
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
        $post = Post::with([
            'author',
            'category',
            'tags' => function ($query) {
                // Only load tags with valid name and slug
                $query->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->select('id', 'name', 'slug');
            }
        ])
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return $this->successResponse(
            new BlogPostResource($post)
        );
    }

    /**
     * Get list of blog categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_visible', true)
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
}