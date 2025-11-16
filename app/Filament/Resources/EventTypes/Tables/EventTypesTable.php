<?php

namespace App\Filament\Resources\EventTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;

class EventTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('webp')
                    ->label('Thumbnail'),
                Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Columns\TextColumn::make('slug')
                    ->searchable()
                    ->label('Slug'),
                Columns\TextColumn::make('event_count')
                    ->label('Jumlah Event')
                    ->counts('events')
                    ->sortable(),
                Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif')
                    ->sortable(),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
