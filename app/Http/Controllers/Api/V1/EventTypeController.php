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
                ->withCount('events')
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