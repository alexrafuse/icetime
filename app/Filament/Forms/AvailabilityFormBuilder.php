<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Domain\Shared\ValueObjects\DayOfWeek;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Form builder for availability forms
 *
 * Provides reusable form schemas for creating and editing availability records,
 * eliminating duplication across resources.
 */
class AvailabilityFormBuilder
{
    /**
     * Get the area creation form schema
     *
     * @return array<Component>
     */
    public static function areaCreateSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('base_price')
                ->required()
                ->numeric()
                ->prefix('$')
                ->minValue(0),
            Toggle::make('is_active')
                ->required()
                ->default(true),
        ];
    }

    /**
     * Get the day/date selection schema
     */
    public static function dayDateSchema(): Grid
    {
        return Grid::make(2)
            ->schema([
                Select::make('day_of_week')
                    ->options(DayOfWeek::options())
                    ->nullable()
                    ->label('Regular Weekly Day')
                    ->helperText('Leave empty for specific dates'),
                DatePicker::make('date')
                    ->nullable()
                    ->label('Specific Date')
                    ->helperText('Leave empty for regular weekly hours')
                    ->disabled(fn (Get $get): bool => $get('day_of_week') !== null),
            ]);
    }

    /**
     * Get the time slot schema
     */
    public static function timeSlotSchema(): Grid
    {
        return Grid::make(2)
            ->schema([
                TimePicker::make('start_time')
                    ->required()
                    ->seconds(false),
                TimePicker::make('end_time')
                    ->required()
                    ->seconds(false)
                    ->after('start_time'),
            ]);
    }

    /**
     * Get the complete availability form schema
     *
     * @param  bool  $includeArea  Whether to include the area selection field
     * @return array<Component>
     */
    public static function schema(bool $includeArea = true): array
    {
        $components = [];

        if ($includeArea) {
            $components[] = Select::make('area_id')
                ->relationship('area', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm(self::areaCreateSchema());
        }

        $components[] = self::dayDateSchema();
        $components[] = self::timeSlotSchema();
        $components[] = Toggle::make('is_available')
            ->required()
            ->default(true);

        return $components;
    }
}
