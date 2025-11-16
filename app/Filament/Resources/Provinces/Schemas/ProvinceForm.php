<?php

namespace App\Filament\Resources\Provinces\Schemas;

use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProvinceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Provinsi')
                    ->schema([
                        Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Provinsi')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('Slug'),
                        Components\Textarea::make('description')
                            ->rows(3)
                            ->label('Deskripsi')
                            ->helperText('Deskripsi singkat tentang provinsi ini'),
                        Components\SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->collection('default')
                            ->image()
                            ->imageEditor()
                            ->label('Thumbnail')
                            ->helperText('Gambar thumbnail untuk provinsi (akan otomatis dikonversi ke WebP)'),
                        Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Aktif')
                            ->helperText('Nonaktifkan untuk menyembunyikan dari frontend'),
                        Components\Toggle::make('is_featured_frontend')
                            ->default(false)
                            ->label('Tampilkan di Homepage')
                            ->helperText('Aktifkan untuk menampilkan provinsi ini di section homepage.'),
                    ]),
            ]);
    }
}
