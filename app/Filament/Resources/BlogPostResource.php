<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Blog';
    }

    public static function getModelLabel(): string
    {
        return 'Blog';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Blog';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Blog';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationBadge(): ?string
    {
        // Count all posts
        $count = static::getModel()::count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Konten Artikel')
                    ->description('Informasi dasar artikel')
                    ->schema([
                        Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Components\Textarea::make('excerpt')
                            ->label('Kutipan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan artikel untuk preview dan SEO'),
                        Components\RichEditor::make('content')
                            ->label('Konten')
                            ->required()
                            ->helperText('Konten lengkap artikel (mendukung HTML)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                                'h2',
                                'h3',
                                'attachFiles',
                            ])
                            ->fileAttachmentsDisk(config('filesystems.default', 'r2'))
                            ->fileAttachmentsDirectory('blog/attachments')
                            ->fileAttachmentsVisibility('public'),
                        Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->label('Gambar Sampul')
                            ->collection('banner')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9', // Google Article recommended
                                '4:3',
                                '1:1',
                            ])
                            ->helperText('Gambar akan otomatis dikonversi ke WebP. Format 16:9 direkomendasikan untuk Google Article.'),
                    ]),
                Section::make('Metadata & Kategori')
                    ->description('Kategori, penulis, dan tag artikel')
                    ->schema([
                        Components\Select::make('blog_author_id')
                            ->label('Penulis')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(BlogAuthor::class, 'email'),
                                Components\Textarea::make('bio')
                                    ->rows(3),
                            ]),
                        Components\Select::make('blog_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(BlogCategory::class, 'slug'),
                                Components\Textarea::make('description')
                                    ->rows(3),
                            ]),
                        Components\TagsInput::make('tags')
                            ->label('Tag')
                            ->helperText('Tag untuk kategorisasi artikel (pisahkan dengan koma)')
                            ->placeholder('contoh: tips lari, marathon, training'),
                    ])
                    ->columns(2),
                Section::make('Publikasi & Frontend')
                    ->description('Pengaturan publikasi dan target frontend')
                    ->schema([
                        Components\DatePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->default(now())
                            ->displayFormat('d/m/Y'),
                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                        Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Tampilkan di halaman utama'),
                        Components\Select::make('is_for_maraton_id')
                            ->label('Target Frontend')
                            ->options([
                                '' => 'Kedua Portal (Default)',
                                '0' => 'Hanya IndonesiaMarathon.com',
                                '1' => 'Hanya Maraton.ID',
                            ])
                            ->default('')
                            ->helperText('Pilih portal dimana artikel ini akan ditampilkan. Default: tampil di kedua portal.')
                            ->nullable()
                            ->afterStateHydrated(function ($component, $state) {
                                // Convert null/boolean ke string untuk Select
                                if ($state === null) {
                                    $component->state('');
                                } elseif ($state === true) {
                                    $component->state('1');
                                } elseif ($state === false) {
                                    $component->state('0');
                                }
                            })
                            ->dehydrateStateUsing(fn ($state) => match ($state) {
                                '' => null,
                                '0' => false,
                                '1' => true,
                                default => null,
                            }),
                    ])
                    ->columns(2),
                Section::make('SEO (Google Article Schema)')
                    ->description('Optimasi untuk mesin pencari (otomatis terisi dari title dan excerpt)')
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Judul untuk SEO (default: menggunakan title artikel)')
                            ->default(fn (Get $get) => $get('title') ?: '')
                            ->placeholder(fn (Get $get) => $get('title') ?: 'Masukkan judul SEO'),
                        Components\Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Deskripsi untuk SEO (otomatis terisi dari excerpt jika kosong)')
                            ->placeholder('Masukkan deskripsi SEO atau biarkan kosong untuk auto-fill'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['author', 'category'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => static::getEloquentQuery())
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('NO')
                    ->rowIndex()
                    ->sortable(false)
                    ->alignCenter(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('banner')
                    ->label('Gambar')
                    ->collection('banner')
                    ->conversion('thumb')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->title)
                    ->wrap(false),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_for_maraton_id')
                    ->label('Frontend')
                    ->badge()
                    ->getStateUsing(fn ($record) => match ($record->is_for_maraton_id) {
                        null => 'both',
                        false => 'indonesiamarathon',
                        true => 'maratonid',
                        default => 'both',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'both' => 'Kedua Portal',
                        'indonesiamarathon' => 'IndonesiaMarathon.com',
                        'maratonid' => 'Maraton.ID',
                        default => 'Kedua Portal',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'both' => 'info',
                        'indonesiamarathon' => 'warning',
                        'maratonid' => 'success',
                        default => 'info',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'both' => 'heroicon-o-globe-alt',
                        'indonesiamarathon' => 'heroicon-o-home',
                        'maratonid' => 'heroicon-o-globe-americas',
                        default => 'heroicon-o-globe-alt',
                    })
                    ->sortable()
                    ->searchable()
                    ->action(
                        Actions\Action::make('change_frontend')
                            ->label('Ubah Frontend')
                            ->icon('heroicon-o-arrow-path')
                            ->form([
                                Components\Select::make('is_for_maraton_id')
                                    ->label('Frontend Target')
                                    ->options([
                                        'both' => 'Kedua Portal',
                                        'indonesiamarathon' => 'IndonesiaMarathon.com',
                                        'maratonid' => 'Maraton.ID',
                                    ])
                                    ->default(fn ($record) => match ($record->is_for_maraton_id) {
                                        null => 'both',
                                        false => 'indonesiamarathon',
                                        true => 'maratonid',
                                        default => 'both',
                                    })
                                    ->required(),
                            ])
                            ->action(function ($record, array $data) {
                                $record->update([
                                    'is_for_maraton_id' => match ($data['is_for_maraton_id']) {
                                        'both' => null,
                                        'indonesiamarathon' => false,
                                        'maratonid' => true,
                                        default => null,
                                    },
                                ]);
                            })
                            ->modalHeading('Ubah Frontend Target')
                            ->modalSubmitActionLabel('Simpan')
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'archived' => 'Archived',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'published' => 'heroicon-o-check-circle',
                        'draft' => 'heroicon-o-document-text',
                        'archived' => 'heroicon-o-archive-box',
                        default => 'heroicon-o-document-text',
                    })
                    ->sortable()
                    ->searchable()
                    ->action(
                        Actions\Action::make('change_status')
                            ->label('Ubah Status')
                            ->icon('heroicon-o-arrow-path')
                            ->form([
                                Components\Select::make('status')
                                    ->label('Status Baru')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default(fn ($record) => $record->status)
                                    ->required(),
                            ])
                            ->action(function ($record, array $data) {
                                $record->update(['status' => $data['status']]);
                            })
                            ->modalHeading('Ubah Status Artikel')
                            ->modalSubmitActionLabel('Simpan')
                    ),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('is_for_maraton_id')
                    ->label('Frontend')
                    ->options([
                        '' => 'Kedua Portal',
                        '0' => 'Hanya IndonesiaMarathon.com',
                        '1' => 'Hanya Maraton.ID',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value']) || $data['value'] === '') {
                            return $query->whereNull('is_for_maraton_id');
                        }
                        return $query->where('is_for_maraton_id', (bool) $data['value']);
                    }),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('blog_author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_status_published')
                        ->label('Set Status: Published')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'published']);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_status_draft')
                        ->label('Set Status: Draft')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'draft']);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_status_archived')
                        ->label('Set Status: Archived')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'archived']);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_both_portals')
                        ->label('Set ke Kedua Portal')
                        ->icon('heroicon-o-globe-alt')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_for_maraton_id' => null]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_indonesiamarathon')
                        ->label('Set ke IndonesiaMarathon.com')
                        ->icon('heroicon-o-home')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_for_maraton_id' => false]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('set_maraton_id')
                        ->label('Set ke Maraton.ID')
                        ->icon('heroicon-o-globe-alt')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_for_maraton_id' => true]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}