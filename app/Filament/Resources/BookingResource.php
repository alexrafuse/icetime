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
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\RecurringPatternResource;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Bookings 🔒';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->default(Auth::id())
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->format('Y-m-d'),
                            
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
                            ->required(),
                            
                        Forms\Components\Textarea::make('setup_instructions')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Recurring Booking')
                    ->schema([
                        Forms\Components\Select::make('recurring_pattern_id')
                            ->relationship(
                                name: 'recurringPattern',
                                titleAttribute: 'title'
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required(),
                                
                                Forms\Components\Hidden::make('user_id')
                                    ->default(fn () => Auth::id()),

                                Forms\Components\Select::make('frequency')
                                    ->options(FrequencyType::class)
                                    ->reactive()
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
                                    ->after('start_date')
                                    ->required(),

                                Forms\Components\CheckboxList::make('days_of_week')
                                    ->options([
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        0 => 'Sunday',
                                    ])
                                    ->columns(4)
                                    ->visible(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY->value)
                                    ->required(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY->value),
                            ])
                            ->editOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required(),
                                
                                Forms\Components\Hidden::make('user_id')
                                    ->default(fn () => Auth::id()),

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
                                    ->after('start_date')
                                    ->required(),

                                Forms\Components\CheckboxList::make('days_of_week')
                                    ->options([
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        0 => 'Sunday',
                                    ])
                                    ->columns(4)
                                    ->visible(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY->value)
                                    ->required(fn (Get $get) => $get('frequency') === FrequencyType::WEEKLY->value),
                            ]),
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