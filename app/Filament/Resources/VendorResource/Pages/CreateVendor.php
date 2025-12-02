<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    protected function afterCreate(): void
    {
        // Ensure all media files are properly saved
        $this->record->refresh();
        $this->record->load('media');
        
        $galleryCount = $this->record->getMedia('gallery')->count();
        if ($galleryCount > 0) {
            Log::info("Vendor created with {$galleryCount} gallery images", [
                'vendor_id' => $this->record->id,
                'gallery_count' => $galleryCount,
            ]);
        }
    }
}

