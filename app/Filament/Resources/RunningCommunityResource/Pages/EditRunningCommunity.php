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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure media is loaded to prevent N+1 queries
        $this->record->load('media');
        return $data;
    }
}

