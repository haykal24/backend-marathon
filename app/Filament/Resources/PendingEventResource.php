<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingEventResource\Pages;
use App\Models\Event;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendingEventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationLabel = 'Event Pending';

    protected static ?string $modelLabel = 'Event Pending';

    protected static ?string $pluralModelLabel = 'Event Pending';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationSort(): ?int
    {
        return 0; // Before EventResource (sort 1)
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Event';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending_review')->count() ?: null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['ratePackage.rateCategory', 'ratePackage.ratePlacement', 'user', 'categories'])
            ->where('status', 'pending_review')
            ->orderBy('created_at', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Event')
                    ->description('Review dan edit informasi event sebelum publish')
                    ->schema([
                        Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Components\RichEditor::make('description')
                            ->required()
                            ->helperText('Deskripsi lengkap event untuk SEO dan informasi peserta'),
                        Components\SpatieMediaLibraryFileUpload::make('image')
                            ->collection('default')
                            ->image()
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
                            ->options(fn () => \App\Models\Province::where('is_active', true)
                                ->pluck('name', 'name')
                                ->toArray()
                            )
                            ->searchable()
                            ->preload(),
                        Components\DatePicker::make('event_date')
                            ->required()
                            ->label('Tanggal Event'),
                        Components\DatePicker::make('event_end_date')
                            ->label('Tanggal Event Berakhir')
                            ->helperText('Untuk event multi-hari (opsional)'),
                        Components\Select::make('event_type')
                            ->required()
                            ->label('Jenis Event')
                            ->options(fn () => \App\Models\EventType::where('is_active', true)
                                ->pluck('name', 'slug')
                                ->toArray()
                            )
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Kategori Event')
                    ->description('Pilih kategori event (bisa lebih dari satu)')
                    ->schema([
                        Components\CheckboxList::make('categories')
                            ->relationship('categories', 'name')
                            ->columns(4),
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
                            ->maxLength(2048),
                        Components\TagsInput::make('benefits')
                            ->label('Benefit Peserta')
                            ->placeholder('Benefit baru...'),
                        Components\KeyValue::make('registration_fees')
                            ->label('Biaya Registrasi')
                            ->keyLabel('Kategori (contoh: 5K)')
                            ->valueLabel('Biaya (contoh: IDR 250.000)'),
                        Components\KeyValue::make('contact_info')
                            ->label('Kontak Event')
                            ->keyLabel('Jenis (contoh: WhatsApp)')
                            ->valueLabel('Info (contoh: 0812 3456 7890)'),
                        Components\KeyValue::make('social_media')
                            ->label('Social Media')
                            ->keyLabel('Platform (contoh: IG)')
                            ->valueLabel('Handle (contoh: @username)'),
                    ]),
                Section::make('Paket Layanan')
                    ->description('Paket promosi yang dipilih pengguna')
                    ->schema([
                        Components\Select::make('rate_package_id')
                            ->relationship('ratePackage', 'name')
                            ->label('Paket Layanan')
                            ->disabled()
                            ->helperText('Paket yang dipilih saat submit event'),
                    ]),
                Section::make('Status & SEO')
                    ->description('Ubah status dan optimasi SEO')
                    ->schema([
                        Components\Select::make('status')
                            ->required()
                            ->label('Status Publikasi')
                            ->options([
                                'draft' => 'Draft',
                                'pending_review' => 'Pending Review',
                                'published' => 'Published',
                            ])
                            ->default('pending_review'),
                        Components\TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Judul untuk SEO (default: menggunakan title event)'),
                        Components\Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Deskripsi untuk SEO'),
                        Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->label('User/Submitter')
                            ->disabled(),
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
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->collection('default')
                    ->conversion('webp'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitter')
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('ratePackage.name')
                    ->label('Paket Layanan')
                    ->badge()
                    ->color('info')
                    ->default('Tidak ada paket')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
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
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dikirim'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Jenis Event')
                    ->options(fn () => \App\Models\EventType::where('is_active', true)->pluck('name', 'slug')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Components\DatePicker::make('created_from')
                            ->label('Dikirim Dari'),
                        Components\DatePicker::make('created_until')
                            ->label('Dikirim Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('approve')
                    ->label('Approve & Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Event')
                    ->modalDescription('Apakah Anda yakin ingin approve dan publish event ini?')
                    ->action(function (Event $record) {
                        $record->update(['status' => 'published']);
                    })
                    ->successNotificationTitle('Event berhasil di-approve dan di-publish'),
                Actions\Action::make('reject')
                    ->label('Reject (Draft)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Event')
                    ->modalDescription('Event akan ditandai sebagai Draft. Apakah Anda yakin?')
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
                    Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'published']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Event berhasil di-approve'),
                    Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => 'draft']))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Event ditandai sebagai Draft'),
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
            'index' => Pages\ListPendingEvents::route('/'),
            'view' => Pages\ViewPendingEvent::route('/{record}'),
            'edit' => Pages\EditPendingEvent::route('/{record}/edit'),
        ];
    }
}