<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Payload berbeda untuk index (ringan) vs detail (lengkap)
     */
    public function toArray(Request $request): array
    {
        $isDetail = $request->routeIs('*.show');

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => $this->getFirstMediaUrl('default', 'webp') ?: null,
            'location_name' => $this->location_name,
            'city' => $this->city,
            'province' => $this->province,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'event_type' => $this->normalizeEventType($this->event_type),
            'is_featured_hero' => (bool) $this->is_featured_hero,
            'featured_hero_expires_at' => $this->featured_hero_expires_at?->toIso8601String(),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
            }),
        ];

        // Detail page - tambahkan field lengkap
        if ($isDetail) {
            $data = array_merge($data, [
                'description' => $this->description,
                'event_end_date' => $this->event_end_date?->format('Y-m-d'),
                'organizer_name' => $this->organizer_name,
                'registration_url' => $this->registration_url,
                'benefits' => $this->benefits,
                'contact_info' => $this->contact_info,
                'registration_fees' => $this->registration_fees,
                'social_media' => $this->social_media,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
                'submitter' => $this->when($this->relationLoaded('user'), function () {
                    return $this->user ? [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ] : null;
                }),
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ]);
        } else {
            // Index page - hanya tambahkan excerpt dari description
            $data['description'] = $this->description 
                ? \Str::limit(strip_tags($this->description), 150) 
                : null;
        }

        return $data;
    }

    private function normalizeEventType(string $type): string
    {
        return match ($type) {
            'road_run' => 'road_run',
            'trail_run' => 'trail_run',
            'fun_run' => 'fun_run',
            'virtual_run' => 'virtual_run',
            'marathon' => 'marathon',
            'half_marathon' => 'half_marathon',
            default => str_replace(' ', '_', strtolower($type)),
        };
    }
}
