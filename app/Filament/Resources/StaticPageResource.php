<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaticPageResource\Pages;
use App\Models\StaticPage;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class StaticPageResource extends Resource
{
    protected static ?string $model = StaticPage::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
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
                Section::make('Konten Halaman')
                    ->description('Konten untuk halaman statis')
                    ->schema([
                        Components\Placeholder::make('placeholders_info')
                            ->label('Template Placeholder')
                            ->content(<<<'MD'
Gunakan placeholder berikut di dalam konten untuk mengisi nilai dinamis dari Site Settings (akan diproses otomatis di frontend):

- {{ site_name }}
- {{ contact_email }}
- {{ contact_whatsapp }} / {{ contact_whatsapp_digits }}
- {{ contact_address }}
- {{ instagram_handle }} / {{ twitter_handle }} / {{ tiktok_handle }}
- {{ facebook_url }}
- {{ last_updated }}
- {{ current_year }}
MD
                            )
                            ->columnSpanFull(),
                        Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Judul Halaman')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->label('Slug URL')
                            ->helperText('URL friendly: tentang-kami, rate-card, dll'),
                        Components\RichEditor::make('content')
                            ->required()
                            ->label('Isi Halaman')
                            ->helperText('Konten lengkap halaman statis'),
                    ]),
                Section::make('SEO')
                    ->description('Optimasi untuk mesin pencari (otomatis terisi dari title dan content)')
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Judul untuk SEO (default: menggunakan title halaman)')
                            ->default(fn (Get $get) => $get('title') ?: '')
                            ->placeholder(fn (Get $get) => $get('title') ?: 'Masukkan judul SEO'),
                        Components\Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Deskripsi untuk SEO (otomatis terisi dari content jika kosong)')
                            ->placeholder('Masukkan deskripsi SEO atau biarkan kosong untuk auto-fill'),
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
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
                    Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaticPages::route('/'),
            'create' => Pages\CreateStaticPage::route('/create'),
            'edit' => Pages\EditStaticPage::route('/{record}/edit'),
        ];
    }
}