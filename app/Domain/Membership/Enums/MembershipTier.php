<?php

declare(strict_types=1);

namespace App\Domain\Membership\Enums;

enum MembershipTier: string
{
    case ACTIVE = 'active';
    case ACTIVE_HALF_YEAR = 'active_half_year';
    case LEAGUE_ONLY = 'league_only';
    case STUDENT = 'student';
    case STICK_CURLING = 'stick_curling';
    case SOCIAL = 'social';

    public function getLevel(): int
    {
        return match ($this) {
            self::ACTIVE => 100,
            self::ACTIVE_HALF_YEAR => 90,
            self::LEAGUE_ONLY => 80,
            self::STUDENT => 70,
            self::STICK_CURLING => 60,
            self::SOCIAL => 10,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ACTIVE_HALF_YEAR => 'Active (Half Year)',
            self::LEAGUE_ONLY => 'League Only',
            self::STUDENT => 'Student',
            self::STICK_CURLING => 'Stick Curling',
            self::SOCIAL => 'Social',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ACTIVE_HALF_YEAR => 'success',
            self::LEAGUE_ONLY => 'info',
            self::STUDENT => 'primary',
            self::STICK_CURLING => 'warning',
            self::SOCIAL => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ACTIVE => 'Full season access with all club privileges',
            self::ACTIVE_HALF_YEAR => 'Half season active membership',
            self::LEAGUE_ONLY => 'Access to one evening league only',
            self::STUDENT => 'Student membership for evening league curling',
            self::STICK_CURLING => 'Stick curling league membership',
            self::SOCIAL => 'Social membership with limited facility access',
        };
    }

    public function grantsActiveStatus(): bool
    {
        return $this !== self::SOCIAL;
    }

    public function isHigherThan(self $other): bool
    {
        return $this->getLevel() > $other->getLevel();
    }
}
