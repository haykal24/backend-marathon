<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Setting')
                    ->schema([
                        // On CREATE page, show a dropdown of available (unused) keys.
                        Components\Select::make('key')
                            ->label('Key')
                            ->required()
                            ->options(fn () => SiteSetting::availableKeyOptions())
                            ->searchable()
                            ->helperText('Pilih key yang belum digunakan. Key tidak dapat diubah setelah dibuat.')
                            ->visible($isCreate)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!$state) return;
                                $definition = SiteSetting::getDefinition($state);
                                if ($definition) {
                                    $set('type', $definition['type'] ?? 'text');
                                    $set('group', $definition['group'] ?? 'general');
                                    $set('description', $definition['description'] ?? '');
                                }
                            }),

                        // On EDIT page, show a disabled text input for the key.
                        Components\TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->disabled()
                            ->visible(!$isCreate),

                        Components\Select::make('type')
                            ->required()
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'image' => 'Image',
                                'json' => 'JSON',
                                'url' => 'URL',
                                'email' => 'Email',
                                'phone' => 'Phone',
                            ])
                            ->default('text')
                            ->label('Tipe')
                            ->live()
                            ->helperText('Tipe data akan terisi otomatis berdasarkan key.')
                            ->disabled(),
                        Components\Textarea::make('value')
                            ->rows(3)
                            ->label('Value')
                            ->visible(fn (Get $get) => in_array($get('type'), ['text', 'textarea', 'url', 'email', 'phone', 'json']))
                            ->helperText(fn (Get $get) => match($get('type')) {
                                'json' => 'Masukkan JSON valid (contoh: {"key": "value"})',
                                default => 'Nilai untuk setting ini',
                            }),
                        Components\SpatieMediaLibraryFileUpload::make('image')
                            ->collection('default')
                            ->image()
                            ->imageEditor()
                            ->label('Image')
                            ->visible(fn (Get $get) => $get('type') === 'image')
                            ->helperText('Upload gambar (akan otomatis dikonversi ke WebP). Field value akan diabaikan untuk type image.'),
                        Components\Select::make('group')
                            ->required()
                            ->options(fn () => SiteSetting::groupOptions())
                            ->default('general')
                            ->label('Group')
                            ->helperText('Kelompok setting untuk organisasi')
                            ->disabled(),
                        Components\Textarea::make('description')
                            ->rows(2)
                            ->label('Deskripsi')
                            ->helperText('Penjelasan singkat tentang setting ini'),
                    ]),
            ]);
    }
}