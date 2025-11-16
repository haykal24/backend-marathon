<?php

namespace App\Filament\Resources\RatePackageResource\Pages;

use App\Filament\Resources\RatePackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRatePackages extends ListRecords
{
    protected static string $resource = RatePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
