<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogAuthorResource\Pages;
use App\Models\BlogAuthor;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogAuthorResource extends Resource
{
    protected static ?string $model = BlogAuthor::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationLabel(): string
    {
        return 'Penulis Blog';
    }

    public static function getModelLabel(): string
    {
        return 'Penulis';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penulis';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Blog';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Penulis')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Components\Textarea::make('bio')
                            ->label('Biografi')
                            ->rows(4),
                        Components\SpatieMediaLibraryFileUpload::make('photo')
                            ->label('Foto')
                            ->collection('photo')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->helperText('Foto penulis (akan otomatis dikonversi ke WebP)'),
                    ]),
                Section::make('Media Sosial')
                    ->description('Link media sosial penulis (opsional)')
                    ->schema([
                        Components\TextInput::make('github_handle')
                            ->label('GitHub Handle')
                            ->maxLength(255)
                            ->prefix('@'),
                        Components\TextInput::make('twitter_handle')
                            ->label('Twitter Handle')
                            ->maxLength(255)
                            ->prefix('@'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')
                    ->label('Foto')
                    ->collection('photo')
                    ->conversion('thumb')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Jumlah Blog')
                    ->counts('posts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
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
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBlogAuthors::route('/'),
        ];
    }
}

