<?php

namespace App\Filament\Resources\RunningCommunityResource\Pages;

use App\Filament\Resources\RunningCommunityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateRunningCommunity extends CreateRecord
{
    protected static string $resource = RunningCommunityResource::class;

    protected function afterCreate(): void
    {
        // Ensure all media files are properly saved
        $this->record->refresh();
        $this->record->load('media');
        
        $galleryCount = $this->record->getMedia('gallery')->count();
        if ($galleryCount > 0) {
            Log::info("Running Community created with {$galleryCount} gallery images", [
                'community_id' => $this->record->id,
                'gallery_count' => $galleryCount,
            ]);
        }
    }
}

