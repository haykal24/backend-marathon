<?php

namespace App\Filament\Resources\BlogAuthorResource\Pages;

use App\Filament\Resources\BlogAuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBlogAuthors extends ManageRecords
{
    protected static string $resource = BlogAuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

