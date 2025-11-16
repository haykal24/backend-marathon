<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProvinceController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // Optimized with caching
        $cacheKey = $request->boolean('featured') ? 'provinces:featured' : 'provinces:all';
        
        $provinces = Cache::remember($cacheKey, 3600, fn () => 
            Province::query()
                ->where('is_active', true)
                ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured_frontend', true))
                ->select(['id', 'name', 'slug', 'description', 'is_featured_frontend'])
                ->withCount('events')
                ->orderBy('name')
                ->get()
                ->map(function ($province) {
                    return new ProvinceResource($province);
                })
        );

        return $this->successResponse($provinces);
    }

    public function show(string $slug): JsonResponse
    {
        $province = Province::where('slug', $slug)
            ->where('is_active', true)
            ->withCount('events')
            ->firstOrFail();

        return $this->successResponse(
            new ProvinceResource($province)
        );
    }
}
