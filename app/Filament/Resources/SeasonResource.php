<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Models\Season;
use App\Filament\Concerns\HasSecurityLabel;
use App\Filament\Resources\SeasonResource\Pages\CreateSeason;
use App\Filament\Resources\SeasonResource\Pages\EditSeason;
use App\Filament\Resources\SeasonResource\Pages\ListSeasons;
use App\Filament\Resources\SeasonResource\Pages\ViewSeason;
use App\Filament\Resources\SeasonResource\RelationManagers\ProductsRelationManager;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    use HasSecurityLabel;

    protected static ?string $model = Season::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Season Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('2025-2026')
                            ->helperText('Season name (e.g., 2025-2026)'),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Season::class, 'slug', ignoreRecord: true)
                            ->placeholder('2025-2026')
                            ->helperText('URL-friendly identifier'),
                    ])
                    ->columns(2),

                Section::make('Season Dates')
                    ->schema([
                        DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->helperText('First day of the season'),

                        DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->after('start_date')
                            ->helperText('Last day of the season'),
                    ])
                    ->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_current')
                            ->label('Current Season')
                            ->helperText('Only one season can be marked as current')
                            ->live(),

                        Toggle::make('is_registration_open')
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('Start Date'),

                TextColumn::make('end_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('End Date'),

                IconColumn::make('is_current')
                    ->boolean()
                    ->label('Current')
                    ->sortable(),

                IconColumn::make('is_registration_open')
                    ->boolean()
                    ->label('Registration Open')
                    ->sortable(),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('success'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_current')
                    ->label('Current Season')
                    ->placeholder('All seasons')
                    ->trueLabel('Current only')
                    ->falseLabel('Not current'),

                TernaryFilter::make('is_registration_open')
                    ->label('Registration Status')
                    ->placeholder('All')
                    ->trueLabel('Open')
                    ->falseLabel('Closed'),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('mark_current')
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

                Action::make('toggle_registration')
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

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Season Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Season Name')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        IconEntry::make('is_current')
                            ->label('Current Season')
                            ->boolean(),

                        IconEntry::make('is_registration_open')
                            ->label('Registration Status')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Season Dates')
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('F j, Y')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('F j, Y')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('duration')
                            ->label('Duration')
                            ->state(function (Season $record) {
                                $start = Carbon::parse($record->start_date);
                                $end = Carbon::parse($record->end_date);

                                return $start->diffInDays($end).' days ('.$start->diffInMonths($end).' months)';
                            })
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('products_count')
                            ->label('Total Products')
                            ->state(fn (Season $record) => $record->products()->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-shopping-bag'),

                        TextEntry::make('available_products_count')
                            ->label('Available Products')
                            ->state(fn (Season $record) => $record->products()->where('is_available', true)->count())
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-check-circle'),

                        TextEntry::make('users_count')
                            ->label('Total Members')
                            ->state(fn (Season $record) => $record->users()->distinct()->count())
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-m-users'),

                        TextEntry::make('total_revenue')
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

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),

                        TextEntry::make('updated_at')
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
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'view' => ViewSeason::route('/{record}'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }
}
