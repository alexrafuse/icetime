<?php

declare(strict_types=1);

namespace App\Domain\Membership\Enums;

enum MembershipCapacity: string
{
    case SINGLE = 'single';
    case COUPLE = 'couple';

    public function isCoupleCapacity(): bool
    {
        return $this === self::COUPLE;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SINGLE => 'Single Member',
            self::COUPLE => 'Couple (2 Members)',
        };
    }

    public function getMaxMembers(): int
    {
        return match ($this) {
            self::SINGLE => 1,
            self::COUPLE => 2,
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SINGLE => 'Membership for one individual',
            self::COUPLE => 'Membership for two adults at the same address',
        };
    }
}
