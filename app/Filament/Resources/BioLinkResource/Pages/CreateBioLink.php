<?php

namespace App\Filament\Resources\BioLinkResource\Pages;

use App\Filament\Resources\BioLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBioLink extends CreateRecord
{
    protected static string $resource = BioLinkResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

