<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RunningCommunityResource extends JsonResource
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
            'location' => $this->location,
            'instagram_handle' => $this->instagram_handle,
            'contact_info' => $this->contact_info,
            'logo_url' => $this->getFirstMediaUrl('default', 'webp') ?: null,
            'is_featured' => $this->is_featured,
            'phone' => $this->getPhoneFromContact($this->contact_info),
            'email' => $this->getEmailFromContact($this->contact_info),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Extract a potential phone number from a string.
     */
    private function getPhoneFromContact(?string $contact): ?string
    {
        if (!$contact) {
            return null;
        }

        // Check if it looks like a phone number (digits, +, spaces, dashes)
        if (preg_match('/^[\d\s\-\+]+$/', $contact) && !filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return $contact;
        }

        return null;
    }

    /**
     * Extract a potential email from a string.
     */
    private function getEmailFromContact(?string $contact): ?string
    {
        if (!$contact) {
            return null;
        }

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return $contact;
        }

        return null;
    }
}