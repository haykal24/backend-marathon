<?php

namespace App\Filament\Resources\RatePlacementResource\Pages;

use App\Filament\Resources\RatePlacementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRatePlacements extends ListRecords
{
    protected static string $resource = RatePlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
