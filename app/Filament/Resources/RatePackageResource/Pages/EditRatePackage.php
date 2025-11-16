<?php

namespace App\Filament\Resources\RatePackageResource\Pages;

use App\Filament\Resources\RatePackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRatePackage extends EditRecord
{
    protected static string $resource = RatePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
