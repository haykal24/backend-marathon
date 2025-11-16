<?php

namespace App\Filament\Resources\Provinces\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;

class ProvincesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('events')->orderBy('events_count', 'desc'))
            ->columns([
                Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->collection('default')
                    ->conversion('webp')
                    ->label('Thumbnail'),
                Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Provinsi'),
                Columns\TextColumn::make('slug')
                    ->searchable()
                    ->label('Slug'),
                Columns\TextColumn::make('events_count')
                    ->label('Jumlah Event')
                    ->sortable(),
                Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),
                Columns\ToggleColumn::make('is_featured_frontend')
                    ->label('Homepage')
                    ->onIcon('heroicon-o-sparkles')
                    ->offIcon('heroicon-o-sparkles')
                    ->onColor('success')
                    ->offColor('gray')
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
