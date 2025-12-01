<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogTagResource\Pages;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Tags\Tag;

class BlogTagResource extends Resource
{
    protected static ?string $model = Tag::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-hashtag';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tag Blog';
    }

    public static function getModelLabel(): string
    {
        return 'Tag';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tag';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Blog';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('type', 'blog')->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Tag')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama Tag')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state)))
                            ->helperText('Nama tag untuk blog (contoh: tips lari, marathon, training). Slug akan otomatis dibuat dari nama tag.'),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Slug otomatis dibuat dari nama tag (bisa diubah manual jika diperlukan)')
                            ->dehydrated(),
                        Components\Hidden::make('type')
                            ->default('blog')
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('type', 'blog'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tag')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('taggables_count')
                    ->label('Digunakan di')
                    ->getStateUsing(function ($record) {
                        return \Illuminate\Support\Facades\DB::table('taggables')
                            ->where('tag_id', $record->id)
                            ->where('taggable_type', 'App\Models\BlogPost')
                            ->count();
                    })
                    ->suffix(' blog'),
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
            'index' => Pages\ManageBlogTags::route('/'),
            'create' => Pages\CreateBlogTag::route('/create'),
            'edit' => Pages\EditBlogTag::route('/{record}/edit'),
        ];
    }
}