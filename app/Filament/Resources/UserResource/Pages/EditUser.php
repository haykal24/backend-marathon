<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika password kosong, hapus dari data agar tidak diupdate
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Hash password jika diisi
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }
}

