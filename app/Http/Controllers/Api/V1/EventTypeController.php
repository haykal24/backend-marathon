<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\EventTypeResource;
use App\Models\EventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class EventTypeController extends BaseApiController
{
    public function index(): JsonResponse
    {
        // Optimized with caching and column selection
        $types = Cache::remember('event_types:all', 3600, fn () => 
            EventType::where('is_active', true)
                ->select(['id', 'name', 'slug', 'description'])
                ->orderBy('name')
                ->get()
        );

        return $this->successResponse(
            EventTypeResource::collection($types)
        );
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
}
