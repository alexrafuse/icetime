<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

/**
 * Represents an immutable date range with start and end dates
 *
 * Ensures that date ranges are always valid (end date is on or after start date)
 * and provides methods for date range operations.
 */
final class DateRange
{
    private readonly Carbon $startDate;

    private readonly Carbon $endDate;

    /**
     * Create a new date range
     *
     * @param  Carbon  $startDate  The start date
     * @param  Carbon  $endDate  The end date
     *
     * @throws InvalidArgumentException If end date is before start date
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException(
                'End date must be on or after start date. Got start: '.$startDate->toDateString().', end: '.$endDate->toDateString()
            );
        }

        $this->startDate = $startDate->clone()->startOfDay();
        $this->endDate = $endDate->clone()->startOfDay();
    }

    /**
     * Create a date range from date strings (Y-m-d format)
     *
     * @param  string  $startDate  The start date
     * @param  string  $endDate  The end date
     */
    public static function fromStrings(string $startDate, string $endDate): self
    {
        return new self(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
    }

    /**
     * Create a date range for a single day
     *
     * @param  Carbon  $date  The date
     */
    public static function singleDay(Carbon $date): self
    {
        return new self($date, $date);
    }

    /**
     * Get the start date
     */
    public function startDate(): Carbon
    {
        return $this->startDate->clone();
    }

    /**
     * Get the end date
     */
    public function endDate(): Carbon
    {
        return $this->endDate->clone();
    }

    /**
     * Get the number of days in this range (inclusive)
     */
    public function numberOfDays(): int
    {
        return $this->startDate->diffInDays($this->endDate) + 1;
    }

    /**
     * Check if a date is within this range (inclusive)
     *
     * @param  Carbon  $date  The date to check
     */
    public function contains(Carbon $date): bool
    {
        $dateOnly = $date->clone()->startOfDay();

        return $dateOnly->gte($this->startDate) && $dateOnly->lte($this->endDate);
    }

    /**
     * Check if this date range overlaps with another
     *
     * @param  self  $other  The other date range
     */
    public function overlaps(self $other): bool
    {
        return $this->startDate->lte($other->endDate) && $this->endDate->gte($other->startDate);
    }

    /**
     * Check if this date range completely contains another date range
     *
     * @param  self  $other  The other date range
     */
    public function containsRange(self $other): bool
    {
        return $this->startDate->lte($other->startDate) && $this->endDate->gte($other->endDate);
    }

    /**
     * Check if this is a single-day range
     */
    public function isSingleDay(): bool
    {
        return $this->startDate->eq($this->endDate);
    }

    /**
     * Get an iterator of all dates in the range
     */
    public function dates(): CarbonPeriod
    {
        return CarbonPeriod::create($this->startDate, $this->endDate);
    }

    /**
     * Get an array of all dates in the range
     *
     * @return array<Carbon>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->dates());
    }

    /**
     * Get a string representation of the date range
     */
    public function toString(): string
    {
        if ($this->isSingleDay()) {
            return $this->startDate->toDateString();
        }

        return $this->startDate->toDateString().' to '.$this->endDate->toDateString();
    }

    /**
     * Get a human-readable representation
     */
    public function toHumanReadable(): string
    {
        if ($this->isSingleDay()) {
            return $this->startDate->format('F j, Y');
        }

        return $this->startDate->format('F j, Y').' to '.$this->endDate->format('F j, Y');
    }

    /**
     * Check if this date range is equal to another
     *
     * @param  self  $other  The other date range
     */
    public function equals(self $other): bool
    {
        return $this->startDate->eq($other->startDate) && $this->endDate->eq($other->endDate);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
