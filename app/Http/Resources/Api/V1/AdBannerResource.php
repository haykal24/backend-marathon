<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->getFirstMediaUrl('default', 'webp') ?: null,
            'target_url' => $this->target_url,
            'slot_location' => $this->slot_location, // Keep original format for API
            'is_active' => $this->is_active,
            'expires_at' => $this->expires_at?->format('Y-m-d\TH:i:s\Z'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
