<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatePackageResource\Pages;
use App\Models\RatePackage;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Str;


class RatePackageResource extends Resource
{
    protected static ?string $model = RatePackage::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Monetisasi & Iklan';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-currency-dollar';
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
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Paket')
                    ->description('Informasi dasar rate package')
                    ->schema([
                        Components\Select::make('rate_category_id')
                            ->relationship('rateCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Kategori Paket')
                            ->helperText('Kelola data kategori melalui menu Rate Categories.'),
                        Components\Select::make('rate_placement_id')
                            ->relationship('ratePlacement', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Lokasi / Placement')
                            ->helperText('Kelola data placement melalui menu Rate Placements.'),
                        Components\TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Digunakan sebagai referensi API / URL.'),
                        Components\Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(4)
                            ->helperText('Ditampilkan pada halaman rate card sebagai highlight paket.'),
                    ]),
                Section::make('Detail Penawaran')
                    ->description('Harga, durasi, audiens, dan deliverables')
                    ->schema([
                        Group::make()
                            ->schema([
                                Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->label('Harga Numerik'),
                                Components\TextInput::make('price_display')
                                    ->maxLength(255)
                                    ->label('Harga Display')
                                    ->helperText('Contoh: Rp 5.000.000 / 30 hari atau Custom Package'),
                            ])
                            ->columns(2),
                        Group::make()
                            ->schema([
                                Components\TextInput::make('duration')
                                    ->maxLength(255)
                                    ->label('Durasi'),
                                Components\TextInput::make('audience')
                                    ->maxLength(255)
                                    ->label('Audiens Utama'),
                            ])
                            ->columns(2),
                        Components\TagsInput::make('deliverables')
                            ->label('Deliverables / Benefit')
                            ->placeholder('Tambahkan benefit...')
                            ->helperText('Setiap item akan ditampilkan sebagai bullet point pada halaman rate card.'),
                        Components\TagsInput::make('channels')
                            ->label('Channel Pendukung')
                            ->placeholder('Contoh: Website, Instagram, Email Newsletter')
                            ->helperText('Channel mana saja package ini tersedia.'),
                        Group::make()
                            ->schema([
                                Components\TextInput::make('cta_label')
                                    ->maxLength(255)
                                    ->label('Label CTA'),
                                Components\TextInput::make('cta_url')
                                    ->url()
                                    ->maxLength(2048)
                                    ->label('URL CTA'),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Pengaturan')
                    ->description('Status dan urutan tampilan')
                    ->schema([
                        Group::make()
                            ->schema([
                                Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                                Components\TextInput::make('order_column')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Urutan Tampilan')
                                    ->helperText('Semakin kecil angka, semakin atas urutannya.'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rateCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('ratePlacement.name')
                    ->label('Lokasi / Channel')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price_display')
                    ->label('Harga Tayang')
                    ->formatStateUsing(fn (?string $state, RatePackage $record) => $state ?: ($record->price ? 'Rp ' . number_format((float) $record->price, 0, ',', '.') : 'Hubungi Kami')),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('order_column')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rate_category_id')
                    ->relationship('rateCategory', 'name')
                    ->label('Kategori'),
                SelectFilter::make('rate_placement_id')
                    ->relationship('ratePlacement', 'name')
                    ->label('Lokasi / Channel'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index' => Pages\ListRatePackages::route('/'),
            'create' => Pages\CreateRatePackage::route('/create'),
            'edit' => Pages\EditRatePackage::route('/{record}/edit'),
        ];
    }
}