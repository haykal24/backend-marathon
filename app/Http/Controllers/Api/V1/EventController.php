<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class EventController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // Optimized query: select only needed columns & eager load relationships
        $query = Event::query()
            ->select(['id', 'title', 'slug', 'description', 'location_name', 'city', 'province', 
                      'event_date', 'event_end_date', 'event_type', 'status', 'is_featured_hero', 'user_id', 'created_at'])
            ->with('categories:id,slug,name');

        // EO dashboard: show own events (any status) when mine=1 (auth required)
        if ($request->boolean('mine') && $request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('status', 'published');
        }

        // Filter by month (format: YYYY-MM, e.g., 2025-11)
        if ($request->has('month') && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);
            $query->whereYear('event_date', $year)->whereMonth('event_date', $month);
        }

        // Filter by type (cached lookup to avoid N+1)
        if ($request->has('type')) {
            $eventType = Cache::rememberForever(
                'event_type:' . $request->type,
                fn () => EventType::where('slug', $request->type)
                    ->where('is_active', true)
                    ->select('id', 'slug', 'name')
                    ->first()
            );
            
            if ($eventType) {
                $query->where('event_type', $eventType->slug);
            }
        }

        // Filter by province (cached lookup)
        if ($request->has('province')) {
            $province = Cache::rememberForever(
                'province:' . $request->province,
                fn () => Province::where('slug', $request->province)
                    ->where('is_active', true)
                    ->select('id', 'name', 'slug')
                    ->first()
            );
            
            if ($province) {
                $query->where('province', $province->name);
            }
        }

        // Filter by city (exact match for better performance)
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Filter by category via whereHas for better performance
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search using only key searchable columns
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location_name', 'like', '%' . $search . '%')
                    ->orWhere('city', $search);
            });
        }

        // Sort with proper indexes
        $sort = $request->get('sort', 'latest'); // latest, upcoming, featured, popular (default: latest)
        match ($sort) {
            'latest' => $query->orderBy('created_at', 'desc'),
            'upcoming' => $query->orderBy('event_date', 'asc'),
            'featured' => $query->where('is_featured_hero', true)->orderBy('event_date', 'asc'),
            'popular' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        // Pagination with optimized per_page
        $perPage = min($request->get('per_page', 12), 50);
        $events = $query->paginate($perPage);

        return $this->paginatedResponse(
            $events,
            EventResource::collection($events)
        );
    }

    public function show(Event $event): JsonResponse
    {
        if ($event->status !== 'published') {
            return $this->errorResponse('Event not found or not published yet.', 404);
        }

        return $this->successResponse(new EventResource($event->load(['categories', 'media'])), 'Event retrieved successfully.');
    }

    /**
     * Get calendar statistics (event count per month for a given year)
     * Query params: year (optional, default: current year)
     */
    public function getCalendarStats(Request $request): JsonResponse
    {
        $year = (int) $request->get('year', now()->year);

        $stats = Cache::remember("calendar_stats_{$year}", 3600, function () use ($year) {
            $monthCounts = [];
            
            for ($month = 1; $month <= 12; $month++) {
                $count = Event::where('status', 'published')
                    ->whereYear('event_date', $year)
                    ->whereMonth('event_date', $month)
                    ->count();
                
                $monthCounts[$month] = $count;
            }
            
            return $monthCounts;
        });

        return $this->successResponse($stats, 'Calendar statistics retrieved successfully.');
    }

    public function getFeaturedHeroEvents(): JsonResponse
    {
        $featuredEvents = Cache::remember('featured_hero_events_v2', 60, function () {
            return Event::with('categories')
                ->where('is_featured_hero', true)
                ->where('status', 'published')
                ->where(function ($query) {
                    $query->whereNull('featured_hero_expires_at')
                        ->orWhere('featured_hero_expires_at', '>', now());
                })
                ->orderBy('event_date', 'asc')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        });

        $featuredEvents->loadMissing('categories');

        return $this->successResponse(
            EventResource::collection($featuredEvents),
            'Featured hero events retrieved successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'event_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_type' => 'required|string',
            'organizer_name' => 'nullable|string|max:255',
            'registration_url' => 'nullable|url|max:2048',
            'benefits' => 'nullable|array',
            'contact_info' => 'nullable|array',
            'registration_fees' => 'nullable|array',
            'social_media' => 'nullable|array',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:event_categories,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'description' => $request->description,
            'location_name' => $request->location_name,
            'city' => $request->city,
            'province' => $request->province ?? null,
            'event_date' => $request->event_date,
            'event_end_date' => $request->event_end_date,
            'event_type' => $request->event_type,
            'organizer_name' => $request->organizer_name,
            'registration_url' => $request->registration_url,
            'benefits' => $request->benefits,
            'contact_info' => $request->contact_info,
            'registration_fees' => $request->registration_fees,
            'social_media' => $request->social_media,
            'status' => 'pending_review',
        ]);

        if ($request->has('categories')) {
            $event->categories()->attach($request->categories);
        }

        return $this->successResponse(
            new EventResource($event->load('categories')),
            'Event berhasil dikirim. Menunggu review admin.',
            201
        );
    }
}