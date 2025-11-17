<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Vendors (pertama di Direktori)
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Direktori & Listing';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Vendor')
                    ->description('Data lengkap vendor')
                    ->schema([
                        Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Vendor'),
                        Components\Select::make('type')
                            ->required()
                            ->options([
                                'medali' => 'Medali',
                                'race_management' => 'Race Management',
                                'jersey' => 'Jersey',
                                'fotografer' => 'Fotografer',
                            ])
                            ->label('Tipe Vendor'),
                        Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->helperText('Deskripsi lengkap tentang vendor'),
                        Components\TextInput::make('city')
                            ->maxLength(100)
                            ->label('Kota')
                            ->helperText('Kota lokasi vendor'),
                        Components\SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('default')
                            ->image()
                            ->label('Logo Vendor')
                            ->helperText('Logo vendor (akan otomatis dikonversi ke WebP)'),
                    ]),
                Section::make('Kontak & Status')
                    ->description('Informasi kontak dan status listing')
                    ->schema([
                        Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->label('Website')
                            ->helperText('URL website vendor'),
                        Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email'),
                        Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(50)
                            ->label('Telepon')
                            ->helperText('Nomor telepon/WhatsApp'),
                        Components\Toggle::make('is_featured')
                            ->label('Featured Listing')
                            ->helperText('Tampilkan sebagai listing berbayar/featured')
                            ->default(false),
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
                Tables\Columns\SpatieMediaLibraryImageColumn::make('logo')
                    ->collection('default')
                    ->conversion('webp'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'medali' => 'Medali',
                        'race_management' => 'Race Management',
                        'jersey' => 'Jersey',
                        'fotografer' => 'Fotografer',
                        default => ucwords(str_replace('_', ' ', $state)),
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'medali' => 'Medali',
                        'race_management' => 'Race Management',
                        'jersey' => 'Jersey',
                        'fotografer' => 'Fotografer',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}