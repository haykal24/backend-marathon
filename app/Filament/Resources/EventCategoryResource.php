<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventCategoryResource\Pages;
use App\Models\EventCategory;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventCategoryResource extends Resource
{
    protected static ?string $model = EventCategory::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationSort(): ?int
    {
        return 2; // Event Categories (setelah Events)
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Event';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('NO')
                    ->rowIndex()
                    ->sortable(false)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('events_count')
                    ->counts('events')
                    ->label('Jumlah Event'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventCategories::route('/'),
            'create' => Pages\CreateEventCategory::route('/create'),
            'edit' => Pages\EditEventCategory::route('/{record}/edit'),
        ];
    }
}