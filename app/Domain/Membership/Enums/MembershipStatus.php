<?php

declare(strict_types=1);

namespace App\Domain\Membership\Enums;

enum MembershipStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::EXPIRED => 'danger',
            self::PENDING => 'warning',
            self::CANCELLED => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ACTIVE => 'Currently active and in good standing',
            self::EXPIRED => 'Membership has expired',
            self::PENDING => 'Awaiting activation or payment',
            self::CANCELLED => 'Membership has been cancelled',
        };
    }
}
