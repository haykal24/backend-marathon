<?php

namespace App\Filament\Resources\PendingEventResource\Pages;

use App\Filament\Resources\PendingEventResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ViewPendingEvent extends ViewRecord
{
    protected static string $resource = PendingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('Approve & Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'published']);
                    $this->redirect(PendingEventResource::getUrl('index'));
                })
                ->successNotificationTitle('Event berhasil di-approve dan di-publish'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Event')
                    ->description('Informasi dasar event')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Judul Event')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\SpatieMediaLibraryImageEntry::make('image')
                            ->collection('default')
                            ->conversion('webp')
                            ->label('Gambar Poster'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Lokasi & Tanggal')
                    ->description('Informasi lokasi dan tanggal pelaksanaan event')
                    ->schema([
                        Infolists\Components\TextEntry::make('location_name')
                            ->label('Nama Lokasi'),
                        Infolists\Components\TextEntry::make('city')
                            ->label('Kota')
                            ->badge(),
                        Infolists\Components\TextEntry::make('province')
                            ->label('Provinsi')
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('event_date')
                            ->label('Tanggal Event')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('event_end_date')
                            ->label('Tanggal Event Berakhir')
                            ->date('d F Y')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d F Y') : 'Event 1 hari'),
                        Infolists\Components\TextEntry::make('event_type')
                            ->label('Jenis Event')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'road_run' => 'Road Run',
                                'trail_run' => 'Trail Run',
                                'fun_run' => 'Fun Run',
                                'virtual_run' => 'Virtual Run',
                                default => ucfirst(str_replace('_', ' ', $state)),
                            }),
                    ])
                    ->columns(3),

                Section::make('Kategori Event')
                    ->description('Kategori event yang dipilih')
                    ->schema([
                        Infolists\Components\TextEntry::make('categories.name')
                            ->label('Kategori Event')
                            ->badge()
                            ->separator(','),
                    ]),

                Section::make('Informasi Tambahan')
                    ->description('Informasi pendukung event')
                    ->schema([
                        Infolists\Components\TextEntry::make('organizer_name')
                            ->label('Nama Organizer/EO')
                            ->default('Tidak tercantum'),
                        Infolists\Components\TextEntry::make('registration_url')
                            ->label('URL Pendaftaran')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab()
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('benefits')
                            ->label('Benefit Peserta')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->default('Tidak ada'),
                        Infolists\Components\KeyValueEntry::make('registration_fees')
                            ->label('Biaya Registrasi')
                            ->keyLabel('Kategori')
                            ->valueLabel('Biaya'),
                        Infolists\Components\KeyValueEntry::make('contact_info')
                            ->label('Kontak Event')
                            ->keyLabel('Jenis')
                            ->valueLabel('Info'),
                        Infolists\Components\KeyValueEntry::make('social_media')
                            ->label('Social Media')
                            ->keyLabel('Platform')
                            ->valueLabel('Handle'),
                    ])
                    ->columns(2),

                Section::make('Informasi Layanan')
                    ->description('Paket promosi yang dipilih pengguna saat submit event')
                    ->schema([
                        Infolists\Components\TextEntry::make('ratePackage.name')
                            ->label('Nama Paket')
                            ->badge()
                            ->color('warning')
                            ->default('Tidak ada paket'),
                        Infolists\Components\TextEntry::make('ratePackage.category')
                            ->label('Kategori Paket')
                            ->badge()
                            ->color('info')
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('ratePackage.description')
                            ->label('Deskripsi Paket')
                            ->markdown()
                            ->default('Tidak ada deskripsi')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('ratePackage.price_display')
                            ->label('Harga')
                            ->badge()
                            ->color('success')
                            ->default('Tidak ada harga'),
                        Infolists\Components\TextEntry::make('ratePackage.duration')
                            ->label('Durasi')
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('ratePackage.audience')
                            ->label('Target Audiens')
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('ratePackage.deliverables')
                            ->label('Deliverables')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->default('Tidak ada deliverables'),
                        Infolists\Components\TextEntry::make('ratePackage.channels')
                            ->label('Channels')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->default('Tidak ada channels'),
                        Infolists\Components\TextEntry::make('ratePackage.rateCategory.name')
                            ->label('Kategori Rate')
                            ->default('Tidak ada'),
                        Infolists\Components\TextEntry::make('ratePackage.ratePlacement.name')
                            ->label('Placement')
                            ->default('Tidak ada'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record->ratePackage),

                Section::make('Status & Pengelola')
                    ->description('Status publikasi dan informasi pengelola')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status Publikasi')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'pending_review' => 'warning',
                                'published' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'draft' => 'Draft',
                                'pending_review' => 'Pending Review',
                                'published' => 'Published',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User/Submitter')
                            ->default('N/A'),
                        Infolists\Components\TextEntry::make('user.phone_number')
                            ->label('No. HP Submitter')
                            ->default('N/A'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email Submitter')
                            ->default('N/A'),
                        Infolists\Components\TextEntry::make('is_featured_hero')
                            ->label('Tampilkan di Hero Slider')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                        Infolists\Components\TextEntry::make('featured_hero_expires_at')
                            ->label('Tanggal Kedaluwarsa Hero Slider')
                            ->dateTime('d F Y, H:i')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d F Y, H:i') : 'Tidak ada'),
                    ])
                    ->columns(3),

                Section::make('SEO')
                    ->description('Optimasi untuk mesin pencari')
                    ->schema([
                        Infolists\Components\TextEntry::make('seo_title')
                            ->label('SEO Title')
                            ->default('Tidak ada')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('seo_description')
                            ->label('SEO Description')
                            ->default('Tidak ada')
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dikirim Pada')
                            ->dateTime('d F Y, H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}