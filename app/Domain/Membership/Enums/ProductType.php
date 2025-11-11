<?php

declare(strict_types=1);

namespace App\Domain\Membership\Enums;

enum ProductType: string
{
    case MEMBERSHIP = 'membership';
    case LEAGUE = 'league';
    case ADDON = 'addon';
    case PROGRAM = 'program';

    public function isMembershipType(): bool
    {
        return $this === self::MEMBERSHIP;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::MEMBERSHIP => 'Membership',
            self::LEAGUE => 'League',
            self::ADDON => 'Add-on',
            self::PROGRAM => 'Program',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MEMBERSHIP => 'success',
            self::LEAGUE => 'info',
            self::ADDON => 'warning',
            self::PROGRAM => 'primary',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::MEMBERSHIP => 'Club membership providing full season access',
            self::LEAGUE => 'League participation and team registration',
            self::ADDON => 'Additional services like lockers and key fobs',
            self::PROGRAM => 'Learn to curl and special programs',
        };
    }
}
