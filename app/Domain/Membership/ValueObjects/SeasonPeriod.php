<?php

declare(strict_types=1);

namespace App\Domain\Membership\ValueObjects;

use Carbon\Carbon;
use Domain\Shared\ValueObjects\DateRange;

/**
 * Represents an immutable season period with season-specific operations
 *
 * Wraps a DateRange and provides season-specific methods for determining
 * if the season is current, upcoming, or past.
 */
final class SeasonPeriod
{
    private readonly DateRange $dateRange;

    /**
     * Create a new season period
     *
     * @param  Carbon  $startDate  The season start date
     * @param  Carbon  $endDate  The season end date
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->dateRange = new DateRange($startDate, $endDate);
    }

    /**
     * Create a season period from date strings (Y-m-d format)
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
     * Create from an existing DateRange
     */
    public static function fromDateRange(DateRange $dateRange): self
    {
        return new self($dateRange->startDate(), $dateRange->endDate());
    }

    /**
     * Get the underlying date range
     */
    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }

    /**
     * Get the start date
     */
    public function startDate(): Carbon
    {
        return $this->dateRange->startDate();
    }

    /**
     * Get the end date
     */
    public function endDate(): Carbon
    {
        return $this->dateRange->endDate();
    }

    /**
     * Check if the season is currently active
     *
     * @param  Carbon|null  $now  The current date (defaults to today)
     */
    public function isCurrent(?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::today();

        return $this->dateRange->contains($now);
    }

    /**
     * Check if the season is in the past
     *
     * @param  Carbon|null  $now  The current date (defaults to today)
     */
    public function isPast(?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::today();

        return $this->endDate()->lt($now->startOfDay());
    }

    /**
     * Check if the season is upcoming/future
     *
     * @param  Carbon|null  $now  The current date (defaults to today)
     */
    public function isUpcoming(?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::today();

        return $this->startDate()->gt($now->startOfDay());
    }

    /**
     * Get the status of the season
     *
     * @return string One of: 'current', 'upcoming', 'past'
     */
    public function getStatus(?Carbon $now = null): string
    {
        if ($this->isCurrent($now)) {
            return 'current';
        }

        if ($this->isUpcoming($now)) {
            return 'upcoming';
        }

        return 'past';
    }

    /**
     * Get a registration window that ends when the season starts
     * Useful for determining if registration is still open
     *
     * @param  int  $daysBeforeStart  Days before season start when registration opens
     * @return DateRange The registration period
     */
    public function registrationWindow(int $daysBeforeStart = 90): DateRange
    {
        $registrationStart = $this->startDate()->subDays($daysBeforeStart);
        $registrationEnd = $this->startDate()->copy();

        return new DateRange($registrationStart, $registrationEnd);
    }

    /**
     * Check if we're in the registration window
     *
     * @param  int  $daysBeforeStart  Days before season start when registration opens
     * @param  Carbon|null  $now  The current date
     */
    public function isInRegistrationWindow(int $daysBeforeStart = 90, ?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::today();

        return $this->registrationWindow($daysBeforeStart)->contains($now);
    }

    /**
     * Get the number of days in the season
     */
    public function numberOfDays(): int
    {
        return $this->dateRange->numberOfDays();
    }

    /**
     * Check if a date is within the season
     *
     * @param  Carbon  $date  The date to check
     */
    public function contains(Carbon $date): bool
    {
        return $this->dateRange->contains($date);
    }

    /**
     * Check if this season overlaps with another
     *
     * @param  self  $other  The other season period
     */
    public function overlaps(self $other): bool
    {
        return $this->dateRange->overlaps($other->dateRange);
    }

    /**
     * Get a string representation of the season period
     */
    public function toString(): string
    {
        return $this->dateRange->toString();
    }

    /**
     * Get a human-readable representation
     */
    public function toHumanReadable(): string
    {
        return $this->dateRange->toHumanReadable();
    }

    /**
     * Get a short format suitable for season names (e.g., "2025-2026")
     */
    public function toSeasonFormat(): string
    {
        $startYear = $this->startDate()->year;
        $endYear = $this->endDate()->year;

        if ($startYear === $endYear) {
            return (string) $startYear;
        }

        return "{$startYear}-{$endYear}";
    }

    /**
     * Check if this season period is equal to another
     *
     * @param  self  $other  The other season period
     */
    public function equals(self $other): bool
    {
        return $this->dateRange->equals($other->dateRange);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
