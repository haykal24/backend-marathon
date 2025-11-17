<?php

namespace App\Filament\Resources\FAQS;

use App\Filament\Resources\FAQS\Pages\ManageFAQS;
use App\Models\FAQ;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FAQResource extends Resource
{
    protected static ?string $model = FAQ::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $recordTitleAttribute = 'question';

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
            ->columns(2)
            ->components([
                Select::make('category')
                    ->label('Category')
                    ->options([
                        'marathon' => 'Marathon',
                        'pace' => 'Pace',
                        'cut_off' => 'Cut Off',
                        'fun_run' => 'Fun Run',
                        'trail_run' => 'Trail Run',
                        'event' => 'Event',
                        'website' => 'Website Info',
                        'general' => 'General',
                    ])
                    ->required()
                    ->columnSpan(1),

                TextInput::make('keyword')
                    ->label('SEO Keyword')
                    ->hint('Reference to SEO keyword (e.g., marathon, pace)')
                    ->columnSpan(1),

                TextInput::make('question')
                    ->label('Question')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('answer')
                    ->label('Answer')
                    ->required()
                    ->rows(5)
                    ->columnSpan(2),

                TextInput::make('related_keyword')
                    ->label('Related Keyword')
                    ->hint('For internal linking to related content')
                    ->columnSpan(1),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('question')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('keyword')
                    ->label('Keyword')
                    ->badge()
                    ->color('info'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('views')
                    ->label('Views')
                    ->numeric(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'marathon' => 'Marathon',
                        'pace' => 'Pace',
                        'cut_off' => 'Cut Off',
                        'fun_run' => 'Fun Run',
                        'trail_run' => 'Trail Run',
                        'event' => 'Event',
                        'website' => 'Website Info',
                        'general' => 'General',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFAQS::route('/'),
        ];
    }
}