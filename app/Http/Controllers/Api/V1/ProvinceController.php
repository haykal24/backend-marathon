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
        // Support pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        $featured = $request->boolean('featured');
        
        $cacheKey = ($featured ? 'provinces:featured' : 'provinces:all') . "_page_{$page}_per_{$perPage}";
        
        $result = Cache::remember($cacheKey, 3600, function () use ($perPage, $page, $featured) {
            $query = Province::query()
                ->where('is_active', true)
                ->when($featured, fn ($q) => $q->where('is_featured_frontend', true))
                ->select(['id', 'name', 'slug', 'description', 'is_featured_frontend'])
                ->withCount('events')
                ->orderBy('name');
            
            // Get total count for pagination
            $total = $query->count();
            
            // Apply pagination
            $provinces = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($province) {
                    return new ProvinceResource($province);
                });
            
            return [
                'data' => $provinces,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ];
        });

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $result['data'],
            $result['total'],
            $result['per_page'],
            $result['current_page'],
            ['path' => $request->url()]
        );
        
        return $this->paginatedResponse($paginator, $result['data']);
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

    public function store(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:provinces,name',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $province = Province::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        Cache::forget('provinces:all');
        Cache::forget('provinces:featured');

        return $this->successResponse(
            new ProvinceResource($province),
            'Province created successfully',
            201
        );
    }
}