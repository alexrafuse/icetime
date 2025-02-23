<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use App\Enums\EventType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use App\Models\RecurringPattern;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RecurringPatternResource\Pages\EditRecurringPattern;
use App\Filament\Resources\RecurringPatternResource\Pages\ListRecurringPatterns;
use App\Filament\Resources\RecurringPatternResource\Pages\CreateRecurringPattern;

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
        return (string) static::getModel()::count();
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
                                ->default(fn(Get $get) => $get('primaryBooking.title')),

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
                                ->native(false),

                            Forms\Components\DatePicker::make('end_date')
                                ->native(false)
                                ->after('start_date'),

                            Forms\Components\CheckboxList::make('days_of_week')
                                ->options([
                                    1 => 'Monday',
                                    2 => 'Tuesday',
                                    3 => 'Wednesday',
                                    4 => 'Thursday',
                                    5 => 'Friday',
                                    6 => 'Saturday',
                                    7 => 'Sunday',
                                ])
                                ->columns(2)
                                ->visible(fn(Forms\Get $get) => $get('frequency') === FrequencyType::WEEKLY->value)
                                ->required(fn(Forms\Get $get) => $get('frequency') === FrequencyType::WEEKLY->value),
                        ])->columns(2),
                ])->columnSpanFull(),

            Forms\Components\Section::make('Booking Details')
                ->schema([
                    Forms\Components\Select::make('primaryBooking.id')
                        ->relationship(
                            name: 'primaryBooking',
                            titleAttribute: 'id'
                        )
                        ->createOptionForm([
                            Forms\Components\TextInput::make('title')
                                ->required(),

                            Forms\Components\DatePicker::make('date')
                                ->required()
                                ->native(false),
                            Forms\Components\TimePicker::make('start_time')
                                ->required()
                                ->native(false),
                            Forms\Components\TimePicker::make('end_time')
                                ->required()
                                ->native(false)
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
                                ->required(),
                            Forms\Components\Textarea::make('setup_instructions')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])
                        ->editOptionForm([
                            Forms\Components\TextInput::make('title')
                                ->required(),

                            //date
                            Forms\Components\DatePicker::make('date')
                                ->required()
                                ->native(false),
                            Forms\Components\TimePicker::make('start_time')
                                ->required()
                                ->native(false),
                            Forms\Components\TimePicker::make('end_time')
                                ->required()
                                ->native(false)
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
                                ->required(),
                            Forms\Components\Textarea::make('setup_instructions')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
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
                        fn(Builder $query): Builder => $query
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
                            $records->each(fn($record) => $record->bookings()->delete());
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
