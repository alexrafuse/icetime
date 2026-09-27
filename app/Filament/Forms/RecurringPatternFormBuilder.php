<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\FrequencyType;
use Domain\Shared\ValueObjects\DayOfWeek;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Form builder for recurring pattern forms
 *
 * Provides reusable form schemas for creating and editing recurring patterns,
 * eliminating duplication across multiple resources.
 */
class RecurringPatternFormBuilder
{
    /**
     * Get the standard recurring pattern form schema
     *
     * @param  bool  $includeTitle  Whether to include the title field
     * @param  bool  $includeHiddenUserId  Whether to include the hidden user_id field
     * @return array<Component>
     */
    public static function schema(bool $includeTitle = true, bool $includeHiddenUserId = true): array
    {
        $components = [];

        if ($includeTitle) {
            $components[] = TextInput::make('title')
                ->required();
        }

        if ($includeHiddenUserId) {
            $components[] = Hidden::make('user_id')
                ->default(fn () => Auth::id());
        }

        $components[] = Select::make('frequency')
            ->options(FrequencyType::class)
            ->live()
            ->required();

        $components[] = TextInput::make('interval')
            ->numeric()
            ->default(1)
            ->minValue(1)
            ->required();

        $components[] = DatePicker::make('start_date')
            ->required()
            ->native(false)
            ->minDate(now())
            ->displayFormat('M d, Y');

        $components[] = DatePicker::make('end_date')
            ->native(false)
            ->minDate(now())
            ->after('start_date')
            ->displayFormat('M d, Y')
            ->required();

        $components[] = CheckboxList::make('days_of_week')
            ->options(DayOfWeek::options())
            ->columns(4)
            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('frequency') === FrequencyType::WEEKLY)
            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('frequency') === FrequencyType::WEEKLY);

        return $components;
    }

    /**
     * Get the create form schema (includes title and user_id)
     *
     * @return array<Component>
     */
    public static function createSchema(): array
    {
        return self::schema(includeTitle: true, includeHiddenUserId: true);
    }

    /**
     * Get the edit form schema (includes title but not user_id)
     *
     * @return array<Component>
     */
    public static function editSchema(): array
    {
        return self::schema(includeTitle: true, includeHiddenUserId: false);
    }
}
