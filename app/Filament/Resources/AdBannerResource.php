<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdBannerResource\Pages;
use App\Models\AdBanner;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;

class AdBannerResource extends Resource
{
    protected static ?string $model = AdBanner::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Ad Banners (pertama di Monetisasi)
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Monetisasi & Iklan';
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
                Section::make('Banner')
                    ->description('Pengaturan banner iklan')
                    ->schema([
                        Components\TextInput::make('name')
                            ->maxLength(255)
                            ->label('Nama Banner')
                            ->helperText('Nama internal untuk identifikasi banner')
                            ->required(),
                        Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->collection('default')
                            ->image()
                            ->required()
                            ->label('Gambar Banner')
                            ->helperText('Gambar banner (akan otomatis dikonversi ke WebP)'),
                        Components\TextInput::make('target_url')
                            ->url()
                            ->maxLength(2048)
                            ->label('URL Target')
                            ->helperText('URL yang akan dibuka saat banner diklik')
                            ->required(),
                        Components\Select::make('slot_location')
                            ->required()
                            ->label('Lokasi Tampil')
                            ->options(AdBanner::slotOptions())
                            ->helperText('Pilih lokasi dimana banner akan ditampilkan'),
                        Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Aktifkan untuk menampilkan banner')
                            ->default(true),
                        Components\DateTimePicker::make('expires_at')
                            ->label('Kedaluwarsa')
                            ->helperText('Tanggal kapan banner tidak aktif lagi (opsional)'),
                    ]),
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
                Tables\Columns\SpatieMediaLibraryImageColumn::make('banner')
                    ->collection('default')
                    ->conversion('webp'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slot_location')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        $options = AdBanner::slotOptions();
                        return $options[$state] ?? ucwords(str_replace('_', ' ', $state));
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('slot_location')
                    ->options(AdBanner::slotOptions()),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListAdBanners::route('/'),
            'create' => Pages\CreateAdBanner::route('/create'),
            'edit' => Pages\EditAdBanner::route('/{record}/edit'),
        ];
    }
}