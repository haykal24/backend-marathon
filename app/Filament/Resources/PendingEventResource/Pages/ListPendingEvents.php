<?php

namespace App\Filament\Resources\PendingEventResource\Pages;

use App\Filament\Resources\PendingEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingEvents extends ListRecords
{
    protected static string $resource = PendingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - pending events are created from frontend
        ];
    }
}

