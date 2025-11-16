<?php

namespace App\Filament\Resources\AdBannerResource\Pages;

use App\Filament\Resources\AdBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAdBanners extends ManageRecords
{
    protected static string $resource = AdBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

