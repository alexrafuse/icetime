<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

/**
 * Represents a day of the week as a value object
 *
 * Provides a type-safe way to work with days of the week,
 * replacing hardcoded integer-to-string mappings throughout the application.
 */
enum DayOfWeek: int
{
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    /**
     * Get all days as an associative array suitable for select options
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::SUNDAY->value => 'Sunday',
            self::MONDAY->value => 'Monday',
            self::TUESDAY->value => 'Tuesday',
            self::WEDNESDAY->value => 'Wednesday',
            self::THURSDAY->value => 'Thursday',
            self::FRIDAY->value => 'Friday',
            self::SATURDAY->value => 'Saturday',
        ];
    }

    /**
     * Get the display name for this day
     */
    public function label(): string
    {
        return match ($this) {
            self::SUNDAY => 'Sunday',
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
        };
    }

    /**
     * Get the short display name (3 letters)
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::SUNDAY => 'Sun',
            self::MONDAY => 'Mon',
            self::TUESDAY => 'Tue',
            self::WEDNESDAY => 'Wed',
            self::THURSDAY => 'Thu',
            self::FRIDAY => 'Fri',
            self::SATURDAY => 'Sat',
        };
    }

    /**
     * Check if this is a weekend day
     */
    public function isWeekend(): bool
    {
        return $this === self::SATURDAY || $this === self::SUNDAY;
    }

    /**
     * Check if this is a weekday
     */
    public function isWeekday(): bool
    {
        return ! $this->isWeekend();
    }

    /**
     * Get the next day
     */
    public function next(): self
    {
        return self::from(($this->value + 1) % 7);
    }

    /**
     * Get the previous day
     */
    public function previous(): self
    {
        return self::from(($this->value + 6) % 7);
    }
}
