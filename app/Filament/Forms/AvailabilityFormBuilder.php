<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Domain\Shared\ValueObjects\DayOfWeek;
use Filament\Forms;

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
     * @return array<Forms\Components\Component>
     */
    public static function areaCreateSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('base_price')
                ->required()
                ->numeric()
                ->prefix('$')
                ->minValue(0),
            Forms\Components\Toggle::make('is_active')
                ->required()
                ->default(true),
        ];
    }

    /**
     * Get the day/date selection schema
     */
    public static function dayDateSchema(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->options(DayOfWeek::options())
                    ->nullable()
                    ->label('Regular Weekly Day')
                    ->helperText('Leave empty for specific dates'),
                Forms\Components\DatePicker::make('date')
                    ->nullable()
                    ->label('Specific Date')
                    ->helperText('Leave empty for regular weekly hours')
                    ->disabled(fn (Forms\Get $get): bool => $get('day_of_week') !== null),
            ]);
    }

    /**
     * Get the time slot schema
     */
    public static function timeSlotSchema(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false),
                Forms\Components\TimePicker::make('end_time')
                    ->required()
                    ->seconds(false)
                    ->after('start_time'),
            ]);
    }

    /**
     * Get the complete availability form schema
     *
     * @param  bool  $includeArea  Whether to include the area selection field
     * @return array<Forms\Components\Component>
     */
    public static function schema(bool $includeArea = true): array
    {
        $components = [];

        if ($includeArea) {
            $components[] = Forms\Components\Select::make('area_id')
                ->relationship('area', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm(self::areaCreateSchema());
        }

        $components[] = self::dayDateSchema();
        $components[] = self::timeSlotSchema();
        $components[] = Forms\Components\Toggle::make('is_available')
            ->required()
            ->default(true);

        return $components;
    }
}
