<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCategoryResource\Pages;
use App\Models\BlogCategory;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-folder';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kategori Blog';
    }

    public static function getModelLabel(): string
    {
        return 'Kategori';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kategori';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Blog';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kategori')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                        Components\Toggle::make('is_visible')
                            ->label('Tampilkan')
                            ->default(true),
                    ]),
                Section::make('SEO')
                    ->description('Optimasi untuk mesin pencari')
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Kosongkan untuk menggunakan nama kategori'),
                        Components\Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Kosongkan untuk menggunakan deskripsi kategori'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Tampilkan')
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
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Tampilkan'),
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
            'index' => Pages\ManageBlogCategories::route('/'),
        ];
    }
}

