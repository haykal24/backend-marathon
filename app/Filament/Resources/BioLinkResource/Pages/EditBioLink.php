<?php

namespace App\Filament\Resources\BioLinkResource\Pages;

use App\Filament\Resources\BioLinkResource;
use Filament\Resources\Pages\EditRecord;

class EditBioLink extends EditRecord
{
    protected static string $resource = BioLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

