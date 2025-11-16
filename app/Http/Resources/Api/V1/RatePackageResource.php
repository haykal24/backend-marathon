<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatePackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->price !== null ? (float) $this->price : null;
        $category = $this->relationLoaded('rateCategory') ? $this->rateCategory : null;
        $placement = $this->relationLoaded('ratePlacement') ? $this->ratePlacement : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'category_label' => $category?->name ?? $this->category,
            'category_description' => $category?->description,
            'category_slug' => $category?->slug,
            'placement_key' => $this->placement_key,
            'placement_label' => $placement?->name ?? $this->placement_key,
            'placement_description' => $placement?->description,
            'placement_slot_key' => $placement?->slot_key,
            'description' => $this->description,
            'price' => $price,
            'price_display' => $this->price_display,
            'price_formatted' => $price !== null ? 'Rp ' . number_format($price, 0, ',', '.') : null,
            'duration' => $this->duration,
            'audience' => $this->audience,
            'deliverables' => $this->deliverables ?? [],
            'channels' => $this->channels ?? [],
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'is_active' => (bool) $this->is_active,
            'order_column' => $this->order_column,
            'category_order' => $category?->order_column,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
