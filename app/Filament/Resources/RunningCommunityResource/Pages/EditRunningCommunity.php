<?php

namespace App\Filament\Resources\RunningCommunityResource\Pages;

use App\Filament\Resources\RunningCommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditRunningCommunity extends EditRecord
{
    protected static string $resource = RunningCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure media is loaded to prevent N+1 queries
        $this->record->load('media');
        return $data;
    }

    protected function afterSave(): void
    {
        // Ensure all media files are properly saved after update
        $this->record->refresh();
        $this->record->load('media');
        
        $galleryCount = $this->record->getMedia('gallery')->count();
        Log::info("Running Community updated with {$galleryCount} gallery images", [
            'community_id' => $this->record->id,
            'gallery_count' => $galleryCount,
        ]);
    }
}

