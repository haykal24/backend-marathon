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
            ->with('categories:id,slug,name')
            ->where('status', 'published'); // Public API: only published events

        // Filter by month (format: YYYY-MM, e.g., 2025-11)
        if ($request->has('month') && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);
            $query->whereYear('event_date', $year)->whereMonth('event_date', $month);
        } elseif ($request->filled('year') && preg_match('/^\d{4}$/', (string) $request->year)) {
            $query->whereYear('event_date', (int) $request->year);
        }

        // Filter by type (support multiple types)
        if ($request->has('type')) {
            $typeSlugs = $request->input('type');
            
            // Handle both array (type[]=a&type[]=b) and single value (type=a)
            if (!is_array($typeSlugs)) {
                $typeSlugs = [$typeSlugs];
            }
            
            // Filter out empty values
            $typeSlugs = array_filter($typeSlugs, fn($slug) => !empty($slug));
            
            if (!empty($typeSlugs)) {
                // Get all valid event types from cache
                $validTypeSlugs = [];
                foreach ($typeSlugs as $slug) {
                    $eventType = Cache::rememberForever(
                        'event_type:' . $slug,
                        fn () => EventType::where('slug', $slug)
                            ->where('is_active', true)
                            ->select('id', 'slug', 'name')
                            ->first()
                    );
                    
                    if ($eventType) {
                        $validTypeSlugs[] = $eventType->slug;
                    }
                }
                
                if (!empty($validTypeSlugs)) {
                    $query->whereIn('event_type', $validTypeSlugs);
                }
            }
        }

        // Filter by province (support multiple provinces)
        if ($request->has('province')) {
            $provinceSlugs = $request->input('province');
            
            // Handle both array (province[]=a&province[]=b) and single value (province=a)
            if (!is_array($provinceSlugs)) {
                $provinceSlugs = [$provinceSlugs];
            }
            
            // Filter out empty values
            $provinceSlugs = array_filter($provinceSlugs, fn($slug) => !empty($slug));
            
            if (!empty($provinceSlugs)) {
                // Get all valid province names from cache
                $validProvinceNames = [];
                foreach ($provinceSlugs as $slug) {
                    $province = Cache::rememberForever(
                        'province:' . $slug,
                        fn () => Province::where('slug', $slug)
                            ->where('is_active', true)
                            ->select('id', 'name', 'slug')
                            ->first()
                    );
                    
                    if ($province) {
                        $validProvinceNames[] = $province->name;
                    }
                }
                
                if (!empty($validProvinceNames)) {
                    $query->whereIn('province', $validProvinceNames);
                }
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
        $allowedOrderColumns = ['event_date', 'created_at', 'updated_at'];
        $orderBy = $request->get('order_by');
        if (!in_array($orderBy, $allowedOrderColumns, true)) {
            $orderBy = null;
        }
        $orderDirection = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        match ($sort) {
            'latest' => $query->orderBy($orderBy ?? 'event_date', $orderDirection),
            'upcoming' => $query->orderBy($orderBy ?? 'event_date', $orderBy ? $orderDirection : 'asc'),
            'featured' => $query->where('is_featured_hero', true)->orderBy($orderBy ?? 'event_date', $orderBy ? $orderDirection : 'asc'),
            'popular' => $query->orderBy($orderBy ?? 'created_at', $orderDirection),
            default => $query->orderBy($orderBy ?? 'event_date', $orderDirection),
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

    /**
     * Get list of years that have published events.
     */
    public function getAvailableYears(): JsonResponse
    {
        $years = Cache::remember('event_available_years', 3600, function () {
            return Event::where('status', 'published')
                ->whereNotNull('event_date')
                ->selectRaw('DISTINCT YEAR(event_date) as year')
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year) => (int) $year)
                ->filter(fn ($year) => $year > 0)
                ->values()
                ->all();
        });

        if (empty($years)) {
            $years = [now()->year];
        }

        return $this->successResponse([
            'years' => $years,
            'min_year' => min($years),
            'max_year' => max($years),
        ], 'Available event years retrieved successfully.');
    }

    /**
     * Get list of cities that have published events.
     * Returns: city, province, event_count
     */
    public function getCities(Request $request): JsonResponse
    {
        // Support pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        
        $cacheKey = "event_cities_list_page_{$page}_per_{$perPage}";
        
        $cities = Cache::remember($cacheKey, 3600, function () use ($perPage, $page) {
            $query = Event::where('status', 'published')
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->select('city', 'province')
                ->selectRaw('COUNT(*) as event_count')
                ->groupBy('city', 'province')
                ->orderByDesc('event_count')
                ->orderBy('city');
            
            // Get total count for pagination
            $total = $query->count();
            
            // Apply pagination
            $items = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($item) {
                    return [
                        'city' => $item->city,
                        'province' => $item->province ?? '',
                        'event_count' => (int) $item->event_count,
                    ];
                })
                ->values()
                ->all();
            
            return [
                'data' => $items,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ];
        });

        // Return paginated response using LengthAwarePaginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $cities['data'],
            $cities['total'],
            $cities['per_page'],
            $cities['current_page'],
            ['path' => $request->url()]
        );
        
        return $this->paginatedResponse($paginator, $cities['data']);
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
        // Security: Ensure user is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        // Decode JSON strings from FormData to arrays
        $data = $request->all();
        
        // Handle benefits
        if ($request->has('benefits') && is_string($request->benefits)) {
            $decoded = json_decode($request->benefits, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['benefits'] = $decoded;
            }
        }
        
        // Handle registration_fees
        if ($request->has('registration_fees') && is_string($request->registration_fees)) {
            $decoded = json_decode($request->registration_fees, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['registration_fees'] = $decoded;
            }
        }
        
        // Handle contact_info
        if ($request->has('contact_info') && is_string($request->contact_info)) {
            $decoded = json_decode($request->contact_info, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['contact_info'] = $decoded;
            }
        }
        
        // Handle social_media
        if ($request->has('social_media') && is_string($request->social_media)) {
            $decoded = json_decode($request->social_media, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['social_media'] = $decoded;
            }
        }

        $validator = Validator::make($data, [
                'rate_package_id' => 'nullable|integer|exists:rate_packages,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000', // Limit description length
            'location_name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'nullable|string|max:100',
            'event_date' => 'required|date|after_or_equal:today', // Prevent past dates
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_type' => 'required|string|exists:event_types,slug',
            'organizer_name' => 'nullable|string|max:255',
            'registration_url' => 'nullable|url|max:2048',
            'benefits' => 'nullable|array|max:20', // Limit array size
            'benefits.*' => 'string|max:255',
            'contact_info' => 'nullable|array|max:10',
            'contact_info.*' => 'nullable|string|max:500',
            'registration_fees' => 'nullable|array|max:20',
            'registration_fees.*' => 'nullable|string|max:100',
            'social_media' => 'nullable|array|max:10',
            'social_media.*' => 'nullable|string|max:255',
            'categories' => 'nullable|array|max:10',
            'categories.*' => 'integer|exists:event_categories,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

            // Sanitize input - Use $data yang sudah di-decode
            $sanitized = [
            'user_id' => $request->user()->id,
                'rate_package_id' => $request->rate_package_id,
                'title' => strip_tags(trim($request->title)),
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'description' => $request->description ? strip_tags(trim($request->description)) : null,
            'location_name' => strip_tags(trim($request->location_name)),
            'city' => strip_tags(trim($request->city)),
            'province' => $request->province ? strip_tags(trim($request->province)) : null,
            'event_date' => $request->event_date,
            'event_end_date' => $request->event_end_date,
            'event_type' => $request->event_type,
            'organizer_name' => $request->organizer_name ? strip_tags(trim($request->organizer_name)) : null,
            'registration_url' => $request->registration_url ? filter_var($request->registration_url, FILTER_SANITIZE_URL) : null,
            'benefits' => isset($data['benefits']) && is_array($data['benefits']) 
                ? array_map('strip_tags', array_map('trim', $data['benefits'])) 
                : null,
            'contact_info' => isset($data['contact_info']) && is_array($data['contact_info'])
                ? array_map(function ($item) {
                    if (is_array($item)) {
                        return array_map('strip_tags', array_map('trim', $item));
                    }
                    return strip_tags(trim($item));
                }, $data['contact_info'])
                : null,
            'registration_fees' => isset($data['registration_fees']) && is_array($data['registration_fees'])
                ? array_map(function ($value) {
                    return is_string($value) ? strip_tags(trim($value)) : $value;
                }, $data['registration_fees'])
                : null,
            'social_media' => isset($data['social_media']) && is_array($data['social_media'])
                ? array_map(function ($item) {
                    if (is_array($item)) {
                        return array_map('strip_tags', array_map('trim', $item));
                    }
                    return strip_tags(trim($item));
                }, $data['social_media'])
                : null,
            'status' => 'pending_review',
        ];

        // Check for duplicate slug
        $existingSlug = Event::where('slug', $sanitized['slug'])->exists();
        if ($existingSlug) {
            $sanitized['slug'] = $sanitized['slug'] . '-' . time();
        }

        $event = Event::create($sanitized);

        // Handle image upload using Spatie Media Library
        if ($request->hasFile('image')) {
            try {
                $event->clearMediaCollection('default');
                $event->addMediaFromRequest('image')
                    ->toMediaCollection('default');
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to upload event image: ' . $e->getMessage());
            }
        }

        // Handle categories - support both array and JSON string
        if ($request->has('categories')) {
            $categories = $request->categories;
            if (is_string($categories)) {
                $categories = json_decode($categories, true);
            }
            if (is_array($categories) && count($categories) > 0) {
                // Validate category IDs exist
                $validCategoryIds = \App\Models\EventCategory::whereIn('id', $categories)->pluck('id')->toArray();
                if (count($validCategoryIds) > 0) {
                    $event->categories()->attach($validCategoryIds);
                }
            }
        }

        return $this->successResponse(
            new EventResource($event->load('categories')),
            'Event berhasil dikirim. Menunggu review admin.',
            201
        );
    }
}