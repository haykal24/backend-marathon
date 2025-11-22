<?php

namespace App\Filament\Resources\BioLinkResource\Pages;

use App\Filament\Resources\BioLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBioLinks extends ListRecords
{
    protected static string $resource = BioLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

