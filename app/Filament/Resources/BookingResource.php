<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;

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
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),
                    ]),
                Forms\Components\Select::make('event_type')
                    ->options([
                        EventType::PRIVATE->value => 'Private',
                        EventType::LEAGUE->value => 'League',
                        EventType::TOURNAMENT->value => 'Tournament',
                    ])
                    ->required(),
                Forms\Components\Select::make('areas')
                    ->multiple()
                    ->relationship(
                        'areas',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->where('is_active', true)
                    )
                    ->preload()
                    ->searchable()
                    ->required()
                    ->saveRelationshipsUsing(function ($record, $state) {
                        if (! $state) return;
                        $record->areas()->sync($state);
                    })
                    ->dehydrated(true)
                    ->afterStateUpdated(function ($state) {
                        Log::info('Areas state updated:', ['state' => $state]);
                    }),
                  
                Forms\Components\Repeater::make('custom_prices')
                    ->schema([
                        Forms\Components\Select::make('areas')
                            ->label('Area')
                            ->options(function (Forms\Get $get) {
                                // Only show selected areas
                                $areaIds = $get('../../areas');
                                if (!$areaIds) return [];
                                return \App\Models\Area::whereIn('id', $areaIds)
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add Custom Price')
                    ->dehydrated(true),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        PaymentStatus::PAID->value => 'Paid',
                        PaymentStatus::UNPAID->value => 'Unpaid',
                        PaymentStatus::PENDING->value => 'Pending',
                    ])
                    ->required()
                    ->default(PaymentStatus::PENDING->value),
                Forms\Components\Textarea::make('setup_instructions')
                    ->nullable()
                    ->columnSpanFull(),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        EventType::PRIVATE->value => 'Private',
                        EventType::LEAGUE->value => 'League',
                        EventType::TOURNAMENT->value => 'Tournament',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        PaymentStatus::PAID->value => 'Paid',
                        PaymentStatus::UNPAID->value => 'Unpaid',
                        PaymentStatus::PENDING->value => 'Pending',
                    ]),
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