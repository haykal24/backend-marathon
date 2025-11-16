<?php

namespace App\Filament\Resources\StaticPageResource\Pages;

use App\Filament\Resources\StaticPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStaticPages extends ManageRecords
{
    protected static string $resource = StaticPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

