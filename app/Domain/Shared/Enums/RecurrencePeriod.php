<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum RecurrencePeriod: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case SEASON = 'season';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::SEASON => 'Per Season',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DAILY => 'Survey resets daily',
            self::WEEKLY => 'Survey resets weekly',
            self::MONTHLY => 'Survey resets monthly',
            self::SEASON => 'Survey resets each curling season',
        };
    }
}
