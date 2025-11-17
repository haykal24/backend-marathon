<?php

namespace App\Filament\Resources\EventTypes;

use App\Filament\Resources\EventTypes\Pages\CreateEventType;
use App\Filament\Resources\EventTypes\Pages\EditEventType;
use App\Filament\Resources\EventTypes\Pages\ListEventTypes;
use App\Filament\Resources\EventTypes\Schemas\EventTypeForm;
use App\Filament\Resources\EventTypes\Tables\EventTypesTable;
use App\Models\EventType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventTypeResource extends Resource
{
    protected static ?string $model = EventType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Event';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return EventTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventTypes::route('/'),
            'create' => CreateEventType::route('/create'),
            'edit' => EditEventType::route('/{record}/edit'),
        ];
    }
}
