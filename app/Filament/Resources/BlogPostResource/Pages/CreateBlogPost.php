<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract tags before save (tags akan di-handle setelah save)
        $this->tags = $data['tags'] ?? [];
        unset($data['tags']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync tags setelah post dibuat
        if (isset($this->tags) && is_array($this->tags)) {
            $this->record->syncTagsFromArray($this->tags);
        }
    }
}

