<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Represents a monetary value with currency
 *
 * Provides a type-safe way to work with money, handling
 * currency and precision concerns in one place.
 */
final class Money
{
    private readonly int $amountInCents;

    private readonly string $currency;

    /**
     * Create a new money value object
     *
     * @param  int  $amountInCents  The amount in the smallest currency unit (cents, pence, etc.)
     * @param  string  $currency  The ISO 4217 currency code (default: CAD)
     *
     * @throws InvalidArgumentException If amount is negative
     */
    public function __construct(int $amountInCents, string $currency = 'CAD')
    {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative. Got: '.$amountInCents);
        }

        $this->amountInCents = $amountInCents;
        $this->currency = strtoupper($currency);
    }

    /**
     * Create a money value from a decimal amount
     *
     * @param  float|string  $amount  The decimal amount (e.g., 19.99)
     * @param  string  $currency  The ISO 4217 currency code (default: CAD)
     */
    public static function fromDecimal(float|string $amount, string $currency = 'CAD'): self
    {
        $amountInCents = (int) round(((float) $amount) * 100);

        return new self($amountInCents, $currency);
    }

    /**
     * Create a zero money value
     *
     * @param  string  $currency  The ISO 4217 currency code (default: CAD)
     */
    public static function zero(string $currency = 'CAD'): self
    {
        return new self(0, $currency);
    }

    /**
     * Get the amount in cents
     */
    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    /**
     * Get the amount as a decimal
     */
    public function amount(): float
    {
        return $this->amountInCents / 100;
    }

    /**
     * Get the currency code
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add another money value
     *
     * @param  self  $other  The other money value
     *
     * @throws InvalidArgumentException If currencies don't match
     */
    public function add(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    /**
     * Subtract another money value
     *
     * @param  self  $other  The other money value
     *
     * @throws InvalidArgumentException If currencies don't match or result would be negative
     */
    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    /**
     * Multiply by a factor
     *
     * @param  float  $multiplier  The multiplier
     */
    public function multiply(float $multiplier): self
    {
        return new self(
            (int) round($this->amountInCents * $multiplier),
            $this->currency
        );
    }

    /**
     * Divide by a divisor
     *
     * @param  float  $divisor  The divisor
     *
     * @throws InvalidArgumentException If divisor is zero
     */
    public function divide(float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide money by zero');
        }

        return new self(
            (int) round($this->amountInCents / $divisor),
            $this->currency
        );
    }

    /**
     * Check if this amount is equal to another
     *
     * @param  self  $other  The other money value
     */
    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    /**
     * Check if this amount is greater than another
     *
     * @param  self  $other  The other money value
     *
     * @throws InvalidArgumentException If currencies don't match
     */
    public function greaterThan(self $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amountInCents > $other->amountInCents;
    }

    /**
     * Check if this amount is less than another
     *
     * @param  self  $other  The other money value
     *
     * @throws InvalidArgumentException If currencies don't match
     */
    public function lessThan(self $other): bool
    {
        $this->ensureSameCurrency($other);

        return $this->amountInCents < $other->amountInCents;
    }

    /**
     * Check if this is zero
     */
    public function isZero(): bool
    {
        return $this->amountInCents === 0;
    }

    /**
     * Check if this is positive (greater than zero)
     */
    public function isPositive(): bool
    {
        return $this->amountInCents > 0;
    }

    /**
     * Format as currency string
     *
     * @param  bool  $includeSymbol  Whether to include currency symbol
     */
    public function format(bool $includeSymbol = true): string
    {
        $symbol = match ($this->currency) {
            'CAD', 'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency.' ',
        };

        $formatted = number_format($this->amount(), 2);

        return $includeSymbol ? $symbol.$formatted : $formatted;
    }

    /**
     * Ensure two money values have the same currency
     *
     * @param  self  $other  The other money value
     *
     * @throws InvalidArgumentException If currencies don't match
     */
    private function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} and {$other->currency}"
            );
        }
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
