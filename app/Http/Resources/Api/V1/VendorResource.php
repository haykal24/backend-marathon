<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->normalizeType($this->type),
            'description' => $this->description,
            'city' => $this->city,
            'logo' => $this->getFirstMediaUrl('default', 'webp') ?: null,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Normalize vendor type to human-readable format
     */
    private function normalizeType(string $type): string
    {
        return match ($type) {
            'medali' => 'Medali',
            'race_management' => 'Race Management',
            'jersey' => 'Jersey',
            'fotografer' => 'Fotografer',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }
}