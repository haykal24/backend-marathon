<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\EventTypeResource;
use App\Models\EventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class EventTypeController extends BaseApiController
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        // Support pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        
        $cacheKey = "event_types:all_page_{$page}_per_{$perPage}";
        
        $result = Cache::remember($cacheKey, 3600, function () use ($perPage, $page) {
            $query = EventType::where('is_active', true)
                ->select(['id', 'name', 'slug', 'description'])
                ->withCount('events')
                ->orderBy('name');
            
            // Get total count for pagination
            $total = $query->count();
            
            // Apply pagination
            $types = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
            
            return [
                'data' => $types,
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
        
        return $this->paginatedResponse($paginator, EventTypeResource::collection($result['data']));
    }

    public function show(string $slug): JsonResponse
    {
        $type = EventType::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->successResponse(
            new EventTypeResource($type)
        );
    }

    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:event_types,name',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $type = EventType::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        Cache::forget('event_types:all');

        return $this->successResponse(
            new EventTypeResource($type),
            'Event type created successfully',
            201
        );
    }
}