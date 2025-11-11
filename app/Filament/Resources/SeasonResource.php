<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Models\Season;
use App\Filament\Concerns\HasSecurityLabel;
use App\Filament\Resources\SeasonResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    use HasSecurityLabel;

    protected static ?string $model = Season::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Membership Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Season Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('2025-2026')
                            ->helperText('Season name (e.g., 2025-2026)'),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Season::class, 'slug', ignoreRecord: true)
                            ->placeholder('2025-2026')
                            ->helperText('URL-friendly identifier'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Season Dates')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->helperText('First day of the season'),

                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->after('start_date')
                            ->helperText('Last day of the season'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_current')
                            ->label('Current Season')
                            ->helperText('Only one season can be marked as current')
                            ->live(),

                        Forms\Components\Toggle::make('is_registration_open')
                            ->label('Registration Open')
                            ->helperText('Allow new member registrations'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('Start Date'),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('End Date'),

                Tables\Columns\IconColumn::make('is_current')
                    ->boolean()
                    ->label('Current')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_registration_open')
                    ->boolean()
                    ->label('Registration Open')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_current')
                    ->label('Current Season')
                    ->placeholder('All seasons')
                    ->trueLabel('Current only')
                    ->falseLabel('Not current'),

                Tables\Filters\TernaryFilter::make('is_registration_open')
                    ->label('Registration Status')
                    ->placeholder('All')
                    ->trueLabel('Open')
                    ->falseLabel('Closed'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('mark_current')
                    ->label('Mark as Current')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Season $record) => $record->is_current)
                    ->action(function (Season $record) {
                        $record->markAsCurrent();
                        Notification::make()
                            ->title('Season marked as current')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('toggle_registration')
                    ->label(fn (Season $record) => $record->is_registration_open ? 'Close Registration' : 'Open Registration')
                    ->icon(fn (Season $record) => $record->is_registration_open ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (Season $record) => $record->is_registration_open ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Season $record) {
                        if ($record->is_registration_open) {
                            $record->closeRegistration();
                            Notification::make()
                                ->title('Registration closed')
                                ->success()
                                ->send();
                        } else {
                            $record->openRegistration();
                            Notification::make()
                                ->title('Registration opened')
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Season Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Season Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        Infolists\Components\IconEntry::make('is_current')
                            ->label('Current Season')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('is_registration_open')
                            ->label('Registration Status')
                            ->boolean(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Season Dates')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('F j, Y')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('F j, Y')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('duration')
                            ->label('Duration')
                            ->state(function (Season $record) {
                                $start = \Carbon\Carbon::parse($record->start_date);
                                $end = \Carbon\Carbon::parse($record->end_date);

                                return $start->diffInDays($end).' days ('.$start->diffInMonths($end).' months)';
                            })
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('products_count')
                            ->label('Total Products')
                            ->state(fn (Season $record) => $record->products()->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-shopping-bag'),

                        Infolists\Components\TextEntry::make('available_products_count')
                            ->label('Available Products')
                            ->state(fn (Season $record) => $record->products()->where('is_available', true)->count())
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-check-circle'),

                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Members')
                            ->state(fn (Season $record) => $record->users()->distinct()->count())
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-m-users'),

                        Infolists\Components\TextEntry::make('total_revenue')
                            ->label('Total Revenue')
                            ->state(function (Season $record) {
                                return $record->products()
                                    ->withCount('users')
                                    ->get()
                                    ->sum(fn ($product) => $product->users_count * ($product->price_cents / 100));
                            })
                            ->money('CAD')
                            ->icon('heroicon-m-currency-dollar')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SeasonResource\RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'view' => Pages\ViewSeason::route('/{record}'),
            'edit' => Pages\EditSeason::route('/{record}/edit'),
        ];
    }
}
