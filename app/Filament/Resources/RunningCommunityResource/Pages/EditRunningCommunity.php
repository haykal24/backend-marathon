<?php

namespace App\Filament\Resources\RunningCommunityResource\Pages;

use App\Filament\Resources\RunningCommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRunningCommunity extends EditRecord
{
    protected static string $resource = RunningCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

