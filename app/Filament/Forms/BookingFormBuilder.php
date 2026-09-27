<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;

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
     * @return array<Component>
     */
    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->required(),

            Select::make('user_id')
                ->default(fn () => auth()->id())
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->dehydrated()
                ->live(),

            DatePicker::make('date')
                ->required()
                ->native(false)
                ->displayFormat('Y-m-d')
                ->format('Y-m-d'),

            TimePicker::make('start_time')
                ->required()
                ->seconds(false),

            TimePicker::make('end_time')
                ->required()
                ->seconds(false)
                ->after('start_time'),

            Select::make('event_type')
                ->options(EventType::class)
                ->required(),

            Select::make('payment_status')
                ->options(PaymentStatus::class)
                ->required(),

            Select::make('areas')
                ->relationship('areas', 'name')
                ->multiple()
                ->preload()
                ->required(),

            Textarea::make('setup_instructions')
                ->nullable()
                ->columnSpanFull(),
        ];
    }

    /**
     * Get the schema wrapped in a Group with columns
     *
     * @param  int  $columns  Number of columns
     */
    public static function groupedSchema(int $columns = 2): Group
    {
        return Group::make()
            ->schema(self::schema())
            ->columns($columns);
    }
}
