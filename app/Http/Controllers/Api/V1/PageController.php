<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\StaticPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends BaseApiController
{
    /**
     * List static pages or fetch by multiple slugs (?slugs=privacy,kontak).
     */
    public function index(Request $request): JsonResponse
    {
        $slugs = collect(explode(',', (string) $request->query('slugs', '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values();

        $query = StaticPage::query()->orderBy('title');

        if ($slugs->isNotEmpty()) {
            $query->whereIn('slug', $slugs->all());
        }

        $pages = $query->get()->map(function (StaticPage $page) {
            return [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
                'created_at' => $page->created_at?->toIso8601String(),
                'updated_at' => $page->updated_at?->toIso8601String(),
            ];
        });

        return $this->successResponse($pages->toArray());
    }

    /**
     * Get static page by slug
     */
    public function show(string $slug): JsonResponse
    {
        $page = StaticPage::where('slug', $slug)->firstOrFail();

        return $this->successResponse([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
            'created_at' => $page->created_at?->toIso8601String(),
            'updated_at' => $page->updated_at?->toIso8601String(),
        ]);
    }
}

