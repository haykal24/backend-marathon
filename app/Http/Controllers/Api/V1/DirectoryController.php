<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\RunningCommunityResource;
use App\Http\Resources\Api\V1\VendorResource;
use App\Models\Vendor;
use App\Models\RunningCommunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectoryController extends BaseApiController
{
    /**
     * Get list of vendors
     */
    public function vendors(Request $request): JsonResponse
    {
        // Optimized query: select only needed columns
        $query = Vendor::query()
            ->select(['id', 'name', 'type', 'description', 'website', 'email', 'phone', 'city', 'is_featured', 'created_at']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter featured only
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search: only key searchable fields
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Sort: featured first, then by name
        $query->orderBy('is_featured', 'desc')
            ->orderBy('name', 'asc');

        // Pagination
        $perPage = min($request->get('per_page', 20), 50); // Max 50 per page
        $vendors = $query->paginate($perPage);

        return $this->paginatedResponse(
            $vendors,
            VendorResource::collection($vendors)
        );
    }

    /**
     * Get list of running communities
     */
    public function runningCommunities(Request $request): JsonResponse
    {
        // Optimized query: select only needed columns
        $query = RunningCommunity::query()
            ->select(['id', 'name', 'location', 'instagram_handle', 'contact_info', 'is_featured', 'created_at']);

        // Filter by location (consolidated field now)
        if ($request->has('city')) {
            $query->where('location', 'like', '%' . $request->city . '%');
        }

        // Filter featured only
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search: only key searchable fields
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Sort: featured first, then by name
        $query->orderBy('is_featured', 'desc')
            ->orderBy('name', 'asc');

        // Pagination
        $perPage = min($request->get('per_page', 20), 50); // Max 50 per page
        $communities = $query->paginate($perPage);

        return $this->paginatedResponse(
            $communities,
            RunningCommunityResource::collection($communities)
        );
    }

    /**
     * Get single vendor by slug
     */
    public function vendorBySlug(string $slug): JsonResponse
    {
        $vendor = Vendor::where('slug', $slug)->firstOrFail();

        return $this->successResponse(
            new VendorResource($vendor)
        );
    }

    /**
     * Get single running community by slug
     */
    public function runningCommunityBySlug(string $slug): JsonResponse
    {
        $community = RunningCommunity::where('slug', $slug)->firstOrFail();

        return $this->successResponse(
            new RunningCommunityResource($community)
        );
    }
}

