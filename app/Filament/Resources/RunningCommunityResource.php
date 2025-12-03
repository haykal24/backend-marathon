<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RunningCommunityResource\Pages;
use App\Models\RunningCommunity;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RunningCommunityResource extends Resource
{
    protected static ?string $model = RunningCommunity::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('media');
    }

    public static function getNavigationSort(): ?int
    {
        return 2; // Running Communities (setelah Vendors)
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
                Section::make('Informasi Komunitas')
                    ->description('Data lengkap komunitas lari')
                    ->schema([
                        Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Komunitas'),
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
                            ->helperText('Deskripsi lengkap tentang komunitas (mendukung format teks dasar)'),
                        Components\TextInput::make('city')
                            ->maxLength(100)
                            ->label('Kota')
                            ->helperText('Kota lokasi komunitas'),
                        Components\Textarea::make('location')
                            ->label('Lokasi Detail')
                            ->rows(3)
                            ->helperText('Alamat atau deskripsi lokasi lengkap komunitas'),
                        Components\TextInput::make('instagram_handle')
                            ->maxLength(100)
                            ->prefix('@')
                            ->label('Instagram')
                            ->helperText('Handle Instagram tanpa @'),
                        Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->label('Website')
                            ->helperText('URL website atau link profil utama komunitas (opsional)'),
                        Components\TextInput::make('contact_info')
                            ->maxLength(255)
                            ->label('Kontak')
                            ->helperText('Informasi kontak (WhatsApp, email, dll)'),
                        Components\SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('default')
                            ->image()
                            ->label('Logo Komunitas')
                            ->helperText('Logo komunitas (akan otomatis dikonversi ke WebP)'),
                        Components\Toggle::make('is_featured')
                            ->label('Featured Listing')
                            ->helperText('Tampilkan sebagai listing berbayar/featured')
                            ->default(false),
                    ]),
                Section::make('Gallery')
                    ->description('Foto-foto aktivitas komunitas, event, atau showcase')
                    ->schema([
                        Components\SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->disk(config('media-library.disk_name', 'r2'))
                            ->label('Gallery Foto')
                            ->helperText('Upload maksimal 10 foto (akan otomatis dikonversi ke WebP)')
                            ->imageEditor()
                            ->reorderable()
                            ->downloadable()
                            ->openable(),
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
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->description)
                    ->width('300px'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->formatStateUsing(fn ($state) => $state ? "@{$state}" : '—'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            'index' => Pages\ListRunningCommunities::route('/'),
            'create' => Pages\CreateRunningCommunity::route('/create'),
            'edit' => Pages\EditRunningCommunity::route('/{record}/edit'),
        ];
    }
}