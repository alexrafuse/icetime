<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use Filament\Forms;

/**
 * Form builder for booking forms
 *
 * Provides reusable form schemas for creating and editing bookings,
 * eliminating duplication across resources and pages.
 */
class BookingFormBuilder
{
    /**
     * Get the standard booking form schema
     *
     * @return array<Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->required(),

            Forms\Components\Select::make('user_id')
                ->default(fn () => auth()->id())
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->dehydrated()
                ->live(),

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
        ];
    }

    /**
     * Get the schema wrapped in a Group with columns
     *
     * @param  int  $columns  Number of columns
     */
    public static function groupedSchema(int $columns = 2): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema(self::schema())
            ->columns($columns);
    }
}
