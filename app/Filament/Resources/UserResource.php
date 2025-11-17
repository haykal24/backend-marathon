<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): ?string
    {
        // Menggunakan prefix karakter untuk mengatur urutan (muncul di bawah)
        return 'Pengaturan Akses';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Users (pertama di Pengaturan Akses)
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
                Section::make('Informasi Pengguna')
                    ->description('Data dasar pengguna')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama')
                            ->maxLength(255)
                            ->helperText('Nama lengkap pengguna'),
                        Components\TextInput::make('phone_number')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->helperText('Nomor WhatsApp untuk login OTP (wajib)'),
                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Email pengguna (opsional)'),
                        Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state, $context) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText(fn (string $context): string => $context === 'create' 
                                ? 'Password untuk login admin (wajib)' 
                                : 'Kosongkan jika tidak ingin mengubah password'),
                    ]),
                Section::make('Roles & Permissions')
                    ->description('Atur role dan permission pengguna')
                    ->schema([
                        Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->label('Roles')
                            ->helperText('Pilih role yang akan diberikan kepada pengguna'),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor WhatsApp'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Roles')
                    ->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Email Verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Role'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}