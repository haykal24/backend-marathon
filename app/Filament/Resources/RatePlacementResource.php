<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatePlacementResource\Pages;
use App\Models\RatePlacement;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

class RatePlacementResource extends Resource
{
    protected static ?string $model = RatePlacement::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Monetisasi & Iklan';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Placement')
                    ->description('Informasi dasar lokasi/placement rate package')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama Placement')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Homepage Hero Slider, Blog Page Header'),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Digunakan untuk referensi dan API.'),
                        Components\TextInput::make('slot_key')
                            ->label('Slot Key (API)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Pastikan sesuai dengan slot yang digunakan frontend / API.'),
                        Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi placement dan spesifikasi untuk admin'),
                    ]),
                Section::make('Catatan & Pengaturan')
                    ->description('Informasi tambahan dan status')
                    ->schema([
                        Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan khusus, limitations, atau informasi penting lainnya'),
                        Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Components\TextInput::make('order_column')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Semakin kecil angka, semakin atas urutannya.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Placement')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slot_key')
                    ->label('Slot Key')
                    ->badge()
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('order_column')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status Aktif'),
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
            'index' => Pages\ListRatePlacements::route('/'),
            'create' => Pages\CreateRatePlacement::route('/create'),
            'edit' => Pages\EditRatePlacement::route('/{record}/edit'),
        ];
    }
}