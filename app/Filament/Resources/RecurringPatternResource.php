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
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RecurringPatternResource extends Resource
{
    protected static ?string $model = RecurringPattern::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Manage';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Recurring Patterns 🔒';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->description('Recurring patterns allow you to create repeating bookings based on a set of rules. When you save changes to a pattern, all future bookings will be regenerated to match the new settings. Existing bookings in the past will remain unchanged.')
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->default(fn (Get $get) => $get('primaryBooking.title')),

                            Forms\Components\Select::make('frequency')
                                ->options(FrequencyType::class)
                                ->required(),

                            Forms\Components\TextInput::make('interval')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),

                            Forms\Components\DatePicker::make('start_date')
                                ->required()
                                ->native(false)
                                ->minDate(now())
                                ->displayFormat('M d, Y'),

                            Forms\Components\DatePicker::make('end_date')
                                ->native(false)
                                ->minDate(now())
                                ->after('start_date')
                                ->displayFormat('M d, Y'),

                            Forms\Components\CheckboxList::make('days_of_week')
                                ->options(DayOfWeek::options())
                                ->columns(2)
                                ->visible(fn (Forms\Get $get) => $get('frequency') === FrequencyType::WEEKLY->value)
                                ->required(fn (Forms\Get $get) => $get('frequency') === FrequencyType::WEEKLY->value),
                        ])->columns(2),
                ])->columnSpanFull(),

            Forms\Components\Section::make('Booking Details')
                ->schema([
                    Forms\Components\Select::make('primary_booking_id')
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('interval')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('days_of_week'),

                Tables\Columns\TextColumn::make('primaryBooking.start_time')
                    ->time()
                    ->sortable()
                    ->label('Start Time'),

                Tables\Columns\TextColumn::make('primaryBooking.end_time')
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
                Tables\Filters\Filter::make('active')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->where('end_date', '>=', now())
                            ->orWhereNull('end_date')
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (RecurringPattern $record) {
                        $record->bookings()->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
