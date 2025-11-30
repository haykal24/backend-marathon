<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventType;
use App\Models\Province;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Events (utama)
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Event';
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
                Section::make('Informasi Event')
                    ->description('Informasi dasar event')
                    ->schema([
                        Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                        Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Components\RichEditor::make('description')
                            ->required()
                            ->helperText('Deskripsi lengkap event untuk SEO dan informasi peserta')
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
                            ->fileAttachmentsDirectory('events/attachments')
                            ->fileAttachmentsVisibility('public'),
                        Components\SpatieMediaLibraryFileUpload::make('image')
                            ->collection('default')
                            ->image()
                            ->required()
                            ->helperText('Gambar poster event (akan otomatis dikonversi ke WebP)'),
                    ]),
                Section::make('Lokasi & Tanggal')
                    ->description('Informasi lokasi dan tanggal pelaksanaan event')
                    ->schema([
                        Components\TextInput::make('location_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Lokasi'),
                        Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100)
                            ->label('Kota'),
                        Components\Select::make('province')
                            ->label('Provinsi')
                            ->options(fn () => Province::where('is_active', true)
                                ->whereNotNull('name')
                                ->where('name', '!=', '')
                                ->get()
                                ->filter(fn ($province) => !empty($province->name))
                                ->mapWithKeys(fn ($province) => [$province->name => $province->name])
                                ->filter()
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih provinsi dari daftar yang tersedia'),
                        Components\DatePicker::make('event_date')
                            ->required()
                            ->label('Tanggal Event'),
                        Components\DatePicker::make('event_end_date')
                            ->label('Tanggal Event Berakhir')
                            ->helperText('Untuk event multi-hari (opsional)'),
                        Components\Select::make('event_type')
                            ->required()
                            ->label('Jenis Event')
                            ->options(fn () => EventType::where('is_active', true)
                                ->whereNotNull('name')
                                ->whereNotNull('slug')
                                ->where('name', '!=', '')
                                ->get()
                                ->filter(fn ($type) => !empty($type->name) && !empty($type->slug))
                                ->mapWithKeys(fn ($type) => [$type->slug => $type->name])
                                ->filter()
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih jenis event dari daftar yang tersedia'),
                    ]),
                Section::make('Kategori Event')
                    ->description('Pilih kategori event (bisa lebih dari satu)')
                    ->schema([
                        Components\CheckboxList::make('categories')
                            ->relationship('categories', 'name')
                            ->required()
                            ->columns(4) // Grid 4 kolom untuk checkbox list
                            ->gridDirection('row'),
                    ]),
                Section::make('Informasi Tambahan')
                    ->description('Informasi pendukung event')
                    ->schema([
                        Components\TextInput::make('organizer_name')
                            ->label('Nama Organizer/EO')
                            ->maxLength(255),
                        Components\TextInput::make('registration_url')
                            ->label('URL Pendaftaran')
                            ->url()
                            ->maxLength(2048)
                            ->helperText('Link pendaftaran eksternal (jika ada)'),
                        TagsInput::make('benefits')
                            ->label('Benefit Peserta')
                            ->helperText('Masukkan benefit dan tekan enter. Contoh: Jersey, Medali')
                            ->placeholder('Benefit baru...'),
                        Components\KeyValue::make('registration_fees')
                            ->label('Biaya Registrasi')
                            ->keyLabel('Kategori (contoh: 5K)')
                            ->valueLabel('Biaya (contoh: IDR 250.000)')
                            ->helperText('Tambahkan biaya untuk setiap kategori lari'),
                        Components\KeyValue::make('contact_info')
                            ->label('Kontak Event')
                            ->keyLabel('Jenis (contoh: WhatsApp)')
                            ->valueLabel('Info (contoh: 0812 3456 7890)')
                            ->helperText('Informasi kontak untuk pertanyaan'),
                        Components\KeyValue::make('social_media')
                            ->label('Social Media')
                            ->keyLabel('Platform (contoh: IG)')
                            ->valueLabel('Handle (contoh: @username)')
                            ->helperText('Akun media sosial event'),
                    ]),
                Section::make('Status & Pengelola')
                    ->description('Status publikasi dan informasi pengelola')
                    ->schema([
                        Components\Select::make('status')
                            ->required()
                            ->label('Status Publikasi')
                            ->options([
                                'draft' => 'Draft',
                                'pending_review' => 'Pending Review',
                                'published' => 'Published',
                            ])
                            ->default('pending_review')
                            ->helperText('Pilih status publikasi event'),
                        Components\Select::make('user_id')
                            ->relationship('user', 'name', fn ($query) => $query->whereNotNull('name')->where('name', '!=', ''))
                            ->searchable()
                            ->preload()
                            ->label('User/Submitter')
                            ->helperText('User yang mengirim event ini (jika ada)'),
                        Components\Toggle::make('is_featured_hero')
                            ->label('Tampilkan di Hero Slider')
                            ->helperText('Aktifkan untuk menampilkan event ini di slider utama homepage.')
                            ->default(false)
                            ->reactive(),
                        Components\DateTimePicker::make('featured_hero_expires_at')
                            ->label('Tanggal Kedaluwarsa Hero Slider')
                            ->helperText('Biarkan kosong jika tidak ingin menetapkan tanggal kedaluwarsa.')
                            ->seconds(false)
                            ->visible(fn (Get $get) => (bool) $get('is_featured_hero')),
                    ]),
                Section::make('SEO')
                    ->description('Optimasi untuk mesin pencari (otomatis terisi dari title dan description)')
                    ->schema([
                        Components\TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Judul untuk SEO (default: menggunakan title event)')
                            ->default(fn (Get $get) => $get('title') ?: '')
                            ->placeholder(fn (Get $get) => $get('title') ?: 'Masukkan judul SEO'),
                        Components\Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Deskripsi untuk SEO (otomatis terisi dari description jika kosong)')
                            ->placeholder('Masukkan deskripsi SEO atau biarkan kosong untuk auto-fill'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('event_date')->orderByDesc('id'))
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('NO')
                    ->rowIndex()
                    ->sortable(false)
                    ->alignCenter(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('webp'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('province')
                    ->searchable()
                    ->sortable()
                    ->label('Provinsi'),
                Tables\Columns\TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'road_run' => 'Road Run',
                        'trail_run' => 'Trail Run',
                        'fun_run' => 'Fun Run',
                        'virtual_run' => 'Virtual Run',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'road_run' => 'success',
                        'trail_run' => 'warning',
                        'fun_run' => 'info',
                        'virtual_run' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('is_featured_hero')
                    ->label('Hero Slider')
                    ->sortable()
                    ->onIcon('heroicon-o-sparkles')
                    ->offIcon('heroicon-o-sparkles')
                    ->onColor('success')
                    ->offColor('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Published',
                        'pending_review' => 'Pending Review',
                        'draft' => 'Draft',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'pending_review' => 'warning',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'published' => 'heroicon-o-check-circle',
                        'pending_review' => 'heroicon-o-clock',
                        'draft' => 'heroicon-o-document-text',
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
                                        'published' => 'Published',
                                        'pending_review' => 'Pending Review',
                                        'draft' => 'Draft',
                                    ])
                                    ->default(fn ($record) => $record->status)
                                    ->required(),
                            ])
                            ->action(function ($record, array $data) {
                                $record->update(['status' => $data['status']]);
                            })
                            ->modalHeading('Ubah Status Event')
                            ->modalSubmitActionLabel('Simpan')
                    ),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitter')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending Review',
                        'published' => 'Published',
                    ]),
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Jenis Event')
                    ->options(fn () => EventType::where('is_active', true)->pluck('name', 'slug'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('province')
                    ->label('Provinsi')
                    ->options(fn () => Province::where('is_active', true)->pluck('name', 'name'))
                    ->searchable(),
                Tables\Filters\Filter::make('event_date')
                    ->form([
                        Components\DatePicker::make('created_from'),
                        Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('event_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('event_date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('hero_slider_active')
                    ->label('Hero Slider Aktif')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_featured_hero', true)
                        ->where(function (Builder $query) {
                            $query->whereNull('featured_hero_expires_at')
                                ->orWhere('featured_hero_expires_at', '>', now());
                        })
                    )
                    ->toggle(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Event $record) => $record->status !== 'published')
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        $record->update(['status' => 'published']);
                    })
                    ->successNotificationTitle('Event dipublish'),
                Actions\Action::make('reject')
                    ->label('Tandai Draft')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Event $record) => $record->status !== 'draft')
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        $record->update(['status' => 'draft']);
                    })
                    ->successNotificationTitle('Event ditandai sebagai Draft'),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['status' => 'published']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Event berhasil di-publish')
                        ->failureNotificationTitle('Sebagian event gagal di-publish. Silakan coba kembali.'),
                ]),
            ]);
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}