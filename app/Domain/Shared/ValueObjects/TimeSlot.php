<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Represents an immutable time slot with start and end times
 *
 * Ensures that time slots are always valid (end time is after start time)
 * and provides methods for time slot operations like overlap detection.
 */
final class TimeSlot
{
    private readonly Carbon $start;

    private readonly Carbon $end;

    /**
     * Create a new time slot
     *
     * @param  Carbon  $start  The start time
     * @param  Carbon  $end  The end time
     *
     * @throws InvalidArgumentException If end time is not after start time
     */
    public function __construct(Carbon $start, Carbon $end)
    {
        if ($end->lte($start)) {
            throw new InvalidArgumentException(
                'End time must be after start time. Got start: '.$start->toTimeString().', end: '.$end->toTimeString()
            );
        }

        $this->start = $start->clone();
        $this->end = $end->clone();
    }

    /**
     * Create a time slot from time strings (HH:MM:SS format)
     *
     * @param  string  $date  The date (Y-m-d format)
     * @param  string  $startTime  The start time
     * @param  string  $endTime  The end time
     */
    public static function fromStrings(string $date, string $startTime, string $endTime): self
    {
        $start = Carbon::parse($date.' '.$startTime);
        $end = Carbon::parse($date.' '.$endTime);

        return new self($start, $end);
    }

    /**
     * Get the start time
     */
    public function start(): Carbon
    {
        return $this->start->clone();
    }

    /**
     * Get the end time
     */
    public function end(): Carbon
    {
        return $this->end->clone();
    }

    /**
     * Get the duration in minutes
     */
    public function durationInMinutes(): int
    {
        return $this->start->diffInMinutes($this->end);
    }

    /**
     * Get the duration in hours
     */
    public function durationInHours(): float
    {
        return $this->durationInMinutes() / 60;
    }

    /**
     * Check if this time slot overlaps with another
     *
     * @param  self  $other  The other time slot
     */
    public function overlaps(self $other): bool
    {
        return $this->start->lt($other->end) && $this->end->gt($other->start);
    }

    /**
     * Check if this time slot contains a specific time
     *
     * @param  Carbon  $time  The time to check
     */
    public function contains(Carbon $time): bool
    {
        return $time->gte($this->start) && $time->lt($this->end);
    }

    /**
     * Check if this time slot completely contains another time slot
     *
     * @param  self  $other  The other time slot
     */
    public function containsSlot(self $other): bool
    {
        return $this->start->lte($other->start) && $this->end->gte($other->end);
    }

    /**
     * Check if this time slot is equal to another
     *
     * @param  self  $other  The other time slot
     */
    public function equals(self $other): bool
    {
        return $this->start->eq($other->start) && $this->end->eq($other->end);
    }

    /**
     * Get a string representation of the time slot
     */
    public function toString(): string
    {
        return $this->start->toTimeString().' - '.$this->end->toTimeString();
    }

    /**
     * Get a human-readable representation
     */
    public function toHumanReadable(): string
    {
        return $this->start->format('g:i A').' - '.$this->end->format('g:i A');
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
