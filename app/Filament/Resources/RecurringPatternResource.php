<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\FrequencyType;
use App\Filament\Forms\BookingFormBuilder;
use App\Filament\Resources\RecurringPatternResource\Pages\CreateRecurringPattern;
use App\Filament\Resources\RecurringPatternResource\Pages\EditRecurringPattern;
use App\Filament\Resources\RecurringPatternResource\Pages\ListRecurringPatterns;
use Domain\Booking\Models\RecurringPattern;
use Domain\Shared\ValueObjects\DayOfWeek;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RecurringPatternResource extends Resource
{
    protected static ?string $model = RecurringPattern::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Recurring Patterns 🔒';
    }

    public static function getRelations(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getRecordWithRelations(): array
    {
        return ['primaryBooking', 'primaryBooking.areas'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->description('Recurring patterns allow you to create repeating bookings based on a set of rules. When you save changes to a pattern, all future bookings will be regenerated to match the new settings. Existing bookings in the past will remain unchanged.')
                ->schema([
                    Group::make()
                        ->schema([
                            TextInput::make('title')
                                ->default(fn (Get $get) => $get('primaryBooking.title')),

                            Select::make('frequency')
                                ->options(FrequencyType::class)
                                ->required(),

                            TextInput::make('interval')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),

                            DatePicker::make('start_date')
                                ->required()
                                ->native(false)
                                ->minDate(now())
                                ->displayFormat('M d, Y'),

                            DatePicker::make('end_date')
                                ->native(false)
                                ->minDate(now())
                                ->after('start_date')
                                ->displayFormat('M d, Y'),

                            CheckboxList::make('days_of_week')
                                ->options(DayOfWeek::options())
                                ->columns(2)
                                ->visible(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY)
                                ->required(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY),
                        ])->columns(2),
                ])->columnSpanFull(),

            Section::make('Booking Details')
                ->schema([
                    Select::make('primary_booking_id')
                        ->relationship(
                            name: 'primaryBooking',
                            titleAttribute: 'id'
                        )
                        ->createOptionForm(BookingFormBuilder::schema())
                        ->editOptionForm(BookingFormBuilder::schema()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('frequency')
                    ->badge()
                    ->sortable(),

                TextColumn::make('interval')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('days_of_week'),

                TextColumn::make('primaryBooking.start_time')
                    ->time()
                    ->sortable()
                    ->label('Start Time'),

                TextColumn::make('primaryBooking.end_time')
                    ->time()
                    ->sortable()
                    ->label('End Time'),

                // Tables\Columns\TextColumn::make('primaryBooking.event_type')
                //     ->badge()
                //     ->sortable()
                //     ->label('Event Type'),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('event_type')
                //     ->options(EventType::class)
                //     ->relationship('primaryBooking', 'event_type'),
                Filter::make('active')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->where('end_date', '>=', now())
                            ->orWhereNull('end_date')
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (RecurringPattern $record) {
                        $record->bookings()->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            $records->each(fn ($record) => $record->bookings()->delete());
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecurringPatterns::route('/'),
            'create' => CreateRecurringPattern::route('/create'),
            'edit' => EditRecurringPattern::route('/{record}/edit'),
        ];
    }
}
