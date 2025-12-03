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
        $isDetailRequest = $request->route('slug') !== null;

        $logoUrl = $this->getFirstMediaUrl('default', 'webp') ?: null;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->normalizeType($this->type),
            'type_raw' => $this->type, // Raw type untuk filtering
            'description' => $this->description,
            'city' => $this->city,
            // Konsistensi dengan RunningCommunityResource & frontend types:
            // kirim baik 'logo' maupun 'logo_url'
            'logo' => $logoUrl,
            'logo_url' => $logoUrl,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        // Add gallery for detail page
        if ($isDetailRequest) {
            $gallery = $this->getMedia('gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl('webp') ?: $media->getUrl(), // Optimized untuk thumbnail
                    'original_url' => $media->getUrl(), // Original full size untuk lightbox
                    'thumb_url' => $media->getUrl('thumb') ?: $media->getUrl(),
                    'name' => $media->name,
                ];
            });

            $data['gallery'] = $gallery->values()->all();
        }

        return $data;
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