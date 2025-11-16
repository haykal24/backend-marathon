<?php

namespace App\Filament\Resources\RunningCommunityResource\Pages;

use App\Filament\Resources\RunningCommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRunningCommunities extends ManageRecords
{
    protected static string $resource = RunningCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

