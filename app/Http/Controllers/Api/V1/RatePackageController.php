<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\RatePackageResource;
use App\Models\RatePackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatePackageController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = RatePackage::query()
            ->with(['rateCategory', 'ratePlacement']);

        if ($request->boolean('is_active', true)) {
            $query->active();
        }

        if ($request->filled('category')) {
            $categorySlug = $request->string('category');
            $query->where(function ($inner) use ($categorySlug) {
                $inner->where('category', $categorySlug)
                    ->orWhereHas('rateCategory', fn ($catQuery) => $catQuery->where('slug', $categorySlug));
            });
        }

        if ($request->filled('placement_key')) {
            $placementKey = $request->string('placement_key');
            $query->where(function ($inner) use ($placementKey) {
                $inner->where('placement_key', $placementKey)
                    ->orWhereHas('ratePlacement', fn ($placementQuery) => $placementQuery->where('slot_key', $placementKey));
            });
        }

        $packages = $query
            ->orderBy('order_column')
            ->orderBy('name')
            ->get();

        // Sort by category order if available
        $packages = $packages->sortBy(function ($package) {
            if ($package->relationLoaded('rateCategory') && $package->rateCategory) {
                return $package->rateCategory->order_column ?? 999;
            }
            return 999;
        })->values();

        return $this->successResponse(
            RatePackageResource::collection($packages)
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $package = RatePackage::query()
            ->with(['rateCategory', 'ratePlacement'])
            ->when($request->boolean('is_active', true), fn ($query) => $query->active())
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->successResponse(new RatePackageResource($package));
    }
}
