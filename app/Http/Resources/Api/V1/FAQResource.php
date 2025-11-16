<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FAQResource extends JsonResource
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
            'category' => $this->category,
            'keyword' => $this->keyword,
            'question' => $this->question,
            'answer' => $this->answer,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'views' => $this->views,
            'related_keyword' => $this->related_keyword,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
