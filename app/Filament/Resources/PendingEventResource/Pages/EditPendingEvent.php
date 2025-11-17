<?php

namespace App\Filament\Resources\PendingEventResource\Pages;

use App\Filament\Resources\PendingEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingEvent extends EditRecord
{
    protected static string $resource = PendingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('approve')
                ->label('Approve & Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Event')
                ->modalDescription('Apakah Anda yakin ingin approve dan publish event ini?')
                ->action(function () {
                    $this->record->update(['status' => 'published']);
                    $this->redirect(PendingEventResource::getUrl('index'));
                })
                ->successNotificationTitle('Event berhasil di-approve dan di-publish'),
            Actions\Action::make('reject')
                ->label('Reject (Draft)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Event')
                ->modalDescription('Event akan ditandai sebagai Draft. Apakah Anda yakin?')
                ->action(function () {
                    $this->record->update(['status' => 'draft']);
                    $this->redirect(PendingEventResource::getUrl('index'));
                })
                ->successNotificationTitle('Event ditandai sebagai Draft'),
        ];
    }
}

