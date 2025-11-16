<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RateCategoryResource\Pages;
use App\Models\RateCategory;
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

class RateCategoryResource extends Resource
{
    protected static ?string $model = RateCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Monetisasi & Iklan';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-rectangle-group';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Informasi dasar kategori rate package')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Social Media Ads, Display Ads'),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Gunakan untuk referensi API dan grouping di frontend.'),
                        Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi kategori untuk referensi admin'),
                    ]),
                Section::make('Pengaturan')
                    ->description('Urutan tampilan dan status')
                    ->schema([
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
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge(),
                TextColumn::make('default_group')
                    ->label('Group')
                    ->badge(),
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
            'index' => Pages\ListRateCategories::route('/'),
            'create' => Pages\CreateRateCategory::route('/create'),
            'edit' => Pages\EditRateCategory::route('/{record}/edit'),
        ];
    }
}