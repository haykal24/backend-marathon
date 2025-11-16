<?php

namespace App\Filament\Resources\RateCategoryResource\Pages;

use App\Filament\Resources\RateCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRateCategories extends ListRecords
{
    protected static string $resource = RateCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
