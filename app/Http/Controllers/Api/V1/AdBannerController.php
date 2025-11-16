<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\AdBannerResource;
use App\Models\AdBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdBannerController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = AdBanner::query();

        // Filter by slot_location
        if ($request->has('slot_location')) {
            $query->where('slot_location', $request->slot_location);
        }

        // Filter by is_active (default true jika tidak di-set)
        $isActive = $request->has('is_active') 
            ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)
            : true;
        
        $query->where('is_active', $isActive);

        // Filter expired banners
        $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });

        $banners = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            AdBannerResource::collection($banners)
        );
    }
}
