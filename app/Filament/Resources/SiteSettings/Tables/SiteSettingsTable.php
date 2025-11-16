<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->label('Key')
                    ->formatStateUsing(fn ($state, $record) => $record?->key_label ?? $state)
                    ->description(fn ($record) => $record?->key),
                Columns\TextColumn::make('value')
                    ->limit(50)
                    ->label('Value')
                    ->visible(fn ($record) => $record && $record->type !== 'image'),
                Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('webp')
                    ->label('Image')
                    ->visible(fn ($record) => $record && $record->type === 'image'),
                Columns\TextColumn::make('type')
                    ->badge()
                    ->label('Tipe')
                    ->sortable(),
                Columns\TextColumn::make('group')
                    ->badge()
                    ->label('Group')
                    ->sortable(),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'image' => 'Image',
                        'json' => 'JSON',
                        'url' => 'URL',
                        'email' => 'Email',
                        'phone' => 'Phone',
                    ]),
                Filters\SelectFilter::make('group')
                    ->options(fn () => SiteSetting::groupOptions()),
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