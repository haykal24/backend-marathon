<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stephenjude\FilamentBlog\Models\Post;
use Illuminate\Support\Str;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Post $this */
        $isDetailRequest = $request->route('slug') !== null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'banner' => $this->banner_url ?: null,
            'content' => $this->when($isDetailRequest, fn () => $this->content),
            'published_at' => $this->published_at instanceof \DateTimeInterface ? $this->published_at->format('Y-m-d H:i:s') : null,
            'author' => $this->when($this->relationLoaded('author'), function () {
                if (! $this->author) {
                    return null;
                }

                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'bio' => $this->author->bio,
                    'photo' => $this->author->photo_url ?: null,
                ];
            }),
            'category' => $this->when($this->relationLoaded('category'), function () {
                if (! $this->category) {
                    return null;
                }

                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'description' => $this->category->description,
                ];
            }),
            'tags' => $this->when(
                $this->relationLoaded('tags'),
                fn () => $this->tags
                    ->filter(function ($tag) {
                        // Filter out tags with empty name
                        return !empty($tag->name) && trim($tag->name) !== '';
                    })
                    ->map(function ($tag) {
                        $name = trim($tag->name ?? '');
                        $slug = is_string($tag->slug) && !empty($tag->slug)
                            ? trim($tag->slug)
                            : (is_array($tag->slug) 
                                ? ($tag->slug['en'] ?? reset($tag->slug)) 
                                : Str::slug($name));

                        return [
                            'id' => $tag->id,
                            'name' => $name,
                            'slug' => $slug ?: Str::slug($name),
                        ];
                    })
                    ->filter(function ($tag) {
                        // Final filter: ensure both name and slug are not empty
                        return !empty($tag['name']) && !empty($tag['slug']);
                    })
                    ->values()
            ),
            'seo_title' => $this->when($isDetailRequest, fn () => $this->seo_title ?? $this->title),
            'seo_description' => $this->when($isDetailRequest, fn () => $this->seo_description ?? $this->excerpt),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}