<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\BioLink;
use Illuminate\Http\JsonResponse;

class BioLinkController extends BaseApiController
{
    /**
     * Get list of active bio links ordered by position.
     */
    public function index(): JsonResponse
    {
        $links = BioLink::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'title' => $link->title,
                    'subtitle' => $link->subtitle,
                    'url' => $link->url,
                    'image' => $link->getFirstMediaUrl('default', 'thumb') ?: null,
                    'image_original' => $link->getFirstMediaUrl('default') ?: null,
                    'order' => $link->order,
                ];
            });

        return $this->successResponse($links);
    }
}

