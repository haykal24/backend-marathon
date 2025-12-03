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
use Illuminate\Database\Eloquent\Builder;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('media');
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
                        Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'undo',
                                'redo',
                            ])
                            ->helperText('Deskripsi lengkap tentang vendor (mendukung format teks dasar)'),
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
                Section::make('Gallery')
                    ->description('Foto-foto produk, portfolio, atau showcase vendor')
                    ->schema([
                        Components\SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->label('Gallery Foto')
                            ->helperText('Upload maksimal 10 foto (akan otomatis dikonversi ke WebP)')
                            ->imageEditor()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->disk(config('media-library.disk_name', 'r2')),
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
                        Components\TextInput::make('instagram_handle')
                            ->maxLength(100)
                            ->prefix('@')
                            ->label('Instagram')
                            ->helperText('Handle Instagram tanpa @'),
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
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->formatStateUsing(fn (?string $state) => $state ? strip_tags($state) : null)
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->description ? strip_tags($record->description) : null)
                    ->width('300px'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'medali' => 'Medali',
                        'race_management' => 'Race Management',
                        'jersey' => 'Jersey',
                        'fotografer' => 'Fotografer',
                        default => ucwords(str_replace('_', ' ', $state)),
                    }),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website')
                    ->label('Website')
                    ->limit(30)
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email copied!'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->copyable()
                    ->copyMessage('Phone copied!'),
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