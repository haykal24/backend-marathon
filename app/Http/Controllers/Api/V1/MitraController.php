<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MitraController extends BaseApiController
{
    /**
     * Get dashboard statistics for authenticated EO/Mitra
     * 
     * Returns:
     * - total_events: Total event yang disubmit user
     * - pending_count: Event dengan status pending_review
     * - published_count: Event dengan status published
     * - draft_count: Event dengan status draft
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $totalEvents = Event::where('user_id', $user->id)->count();
        $pendingCount = Event::where('user_id', $user->id)->where('status', 'pending_review')->count();
        $publishedCount = Event::where('user_id', $user->id)->where('status', 'published')->count();
        $draftCount = Event::where('user_id', $user->id)->where('status', 'draft')->count();

        $stats = [
            'total_events' => $totalEvents,
            'pending_count' => $pendingCount,
            'published_count' => $publishedCount,
            'draft_count' => $draftCount,
        ];

        return $this->successResponse($stats, 'Dashboard statistics retrieved successfully.');
    }

    /**
     * Get all events submitted by authenticated user
     * Supports filtering, sorting, and pagination
     */
    public function myEvents(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $query = Event::query()
            ->select(['id', 'title', 'slug', 'description', 'location_name', 'city', 'province', 
                      'event_date', 'event_end_date', 'event_type', 'status', 'is_featured_hero', 'user_id', 'created_at'])
            ->with('categories:id,slug,name')
            ->where('user_id', $user->id); // Only user's events

        // Filter by status (optional)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search (optional)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location_name', 'like', '%' . $search . '%')
                    ->orWhere('city', $search);
            });
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'latest' => $query->orderBy('created_at', 'desc'),
            'upcoming' => $query->orderBy('event_date', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        // Pagination
        $perPage = min($request->get('per_page', 12), 50);
        $events = $query->paginate($perPage);

        return $this->paginatedResponse(
            $events,
            EventResource::collection($events)
        );
    }

    /**
     * Get a single event submitted by authenticated user
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if (!$user || $event->user_id !== $user->id) {
            return $this->errorResponse('Forbidden', 403);
        }

        // Eager load relationships for detailed view
        $event->load(['categories', 'user', 'ratePackage']);

        return $this->successResponse(new EventResource($event), 'Event retrieved successfully.');
    }
}

