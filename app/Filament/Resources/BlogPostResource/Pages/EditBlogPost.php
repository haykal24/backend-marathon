<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load tags untuk form
        $data['tags'] = $this->record->tags->pluck('name')->toArray();
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract tags before save
        $this->tags = $data['tags'] ?? [];
        unset($data['tags']);
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Sync tags setelah post diupdate
        if (isset($this->tags) && is_array($this->tags)) {
            $this->record->syncTagsFromArray($this->tags);
        }
    }
}

