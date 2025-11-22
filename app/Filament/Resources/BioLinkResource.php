<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BioLinkResource\Pages;
use App\Models\BioLink;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;

class BioLinkResource extends Resource
{
    protected static ?string $model = BioLink::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-link';
    }

    public static function getNavigationLabel(): string
    {
        return 'Bio Links';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
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
                Section::make('Link Details')
                    ->schema([
                        Components\SpatieMediaLibraryFileUpload::make('image')
                            ->label('Icon / Thumbnail')
                            ->collection('default')
                            ->image()
                            ->imageEditor()
                            ->directory('bio-links')
                            ->columnSpanFull(),

                        Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('subtitle')
                            ->label('Subtitle (Opsional)')
                            ->placeholder('Contoh: Info lengkap event...')
                            ->maxLength(255),

                        Components\TextInput::make('url')
                            ->label('URL Tujuan')
                            ->url()
                            ->required()
                            ->prefix('https://')
                            ->columnSpanFull(),

                        Components\TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil muncul lebih atas'),

                        Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->label('Urutan'),
                
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('thumb')
                    ->label('Icon'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Judul'),

                Tables\Columns\TextColumn::make('subtitle')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('url')
                    ->limit(30)
                    ->label('URL'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBioLinks::route('/'),
            'create' => Pages\CreateBioLink::route('/create'),
            'edit' => Pages\EditBioLink::route('/{record}/edit'),
        ];
    }
}