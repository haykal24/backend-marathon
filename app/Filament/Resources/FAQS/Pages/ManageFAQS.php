<?php

namespace App\Filament\Resources\FAQS\Pages;

use App\Filament\Resources\FAQS\FAQResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFAQS extends ManageRecords
{
    protected static string $resource = FAQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
