<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Booking;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Enums\EventType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\RecurringPatternResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Rentals';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->format('Y-m-d')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($get('is_recurring')) {
                                    $set('recurring.start_date', $state);
                                }
                            }),
                            
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->seconds(false),
                            
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),
                            
                        Forms\Components\Select::make('event_type')
                            ->options(EventType::class)
                            ->required(),
                            
                        Forms\Components\Select::make('payment_status')
                            ->options(PaymentStatus::class)
                            ->required(),
                            
                        Forms\Components\Select::make('areas')
                            ->relationship('areas', 'name')
                            ->multiple()
                            ->preload()
                            ->dehydrated(true)
                            ->required(),
                            
                        Forms\Components\Textarea::make('setup_instructions')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Recurring Booking')
                    ->schema([
                        Toggle::make('is_recurring')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if (!$get('is_recurring')) {
                                    $set('recurring.frequency', null);
                                    $set('recurring.interval', null);
                                    $set('recurring.end_date', null);
                                    $set('recurring.days_of_week', null);
                                } else {
                                    $set('recurring.start_date', $get('date'));
                                }
                            }),

                        Section::make()
                            ->schema([
                                Select::make('recurring.frequency')
                                    ->options(FrequencyType::class)
                                    ->required()
                                    ->reactive(),

                                Select::make('recurring.interval')
                                    ->options(fn () => array_combine(range(1, 12), range(1, 12)))
                                    ->default(1)
                                    ->required()
                                    ->label(fn (Get $get) => match ($get('recurring.frequency')) {
                                        FrequencyType::DAILY->value => 'Every X days',
                                        FrequencyType::WEEKLY->value => 'Every X weeks',
                                        FrequencyType::MONTHLY->value => 'Every X months',
                                        default => 'Interval',
                                    }),

                                DatePicker::make('recurring.end_date')
                                    ->required()
                                    ->minDate(fn (Get $get) => $get('date'))
                                    ->date(),

                                Select::make('recurring.days_of_week')
                                    ->multiple()
                                    ->options([
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        0 => 'Sunday',
                                    ])
                                    ->visible(fn (Get $get) => $get('recurring.frequency') === FrequencyType::WEEKLY->value)
                                    ->required(fn (Get $get) => $get('recurring.frequency') === FrequencyType::WEEKLY->value)
                                    ->label('Days of Week'),
                            ])
                            ->visible(fn (Get $get) => $get('is_recurring')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (EventType $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('areas.name')
                    ->badge()
                    ->separator(',')
                    ->wrap(),
                Tables\Columns\IconColumn::make('recurring_pattern_id')
                    ->label('Recurring')
                    ->boolean()
                    ->action(
                        Tables\Actions\Action::make('viewPattern')
                            ->url(fn ($record) => $record->recurring_pattern_id 
                                ? RecurringPatternResource::getUrl('edit', ['record' => $record->recurring_pattern_id])
                                : null)
                            ->visible(fn ($record) => $record->recurring_pattern_id !== null)
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('event_type')
                    ->options(EventType::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('areas')
                    ->relationship('areas', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
} 