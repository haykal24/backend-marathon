<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\EventCategoryResource;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EventCategoryController extends BaseApiController
{
    public function index(): JsonResponse
    {
        // Optimized with caching and column selection
        $categories = Cache::remember('event_categories:all', 3600, fn () => 
            EventCategory::select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get()
        );

        return $this->successResponse(
            EventCategoryResource::collection($categories)
        );
    }

    public function show(string $slug): JsonResponse
    {
        $category = EventCategory::where('slug', $slug)->firstOrFail();

        return $this->successResponse(
            new EventCategoryResource($category)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_categories,name',
        ]);

        $category = EventCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        // Clear cache
        Cache::forget('event_categories:all');

        return $this->successResponse(
            new EventCategoryResource($category),
            'Event category created successfully',
            201
        );
    }
}