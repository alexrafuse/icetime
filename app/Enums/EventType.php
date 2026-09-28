<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case PRIVATE = 'private';
    case LEAGUE = 'league';
    case TOURNAMENT = 'tournament';
    case DROP_IN = 'drop_in';
    case LEARN_TO_CURL = 'learn_to_curl';

    public function getColor(): string
    {
        return match ($this) {
            self::PRIVATE => 'gray',
            self::LEAGUE => 'success',
            self::TOURNAMENT => 'warning',
            self::DROP_IN => 'info',
            self::LEARN_TO_CURL => 'primary',
        };
    }

    public function hexColor(): string
    {
        return match ($this) {
            self::PRIVATE => '#4ade80',
            self::LEAGUE => '#3b82f6',
            self::TOURNAMENT => '#f97316',
            self::DROP_IN => '#06b6d4',
            self::LEARN_TO_CURL => '#a855f7',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::LEAGUE => 'League',
            self::TOURNAMENT => 'Tournament',
            self::DROP_IN => 'Drop-In',
            self::LEARN_TO_CURL => 'Learn to Curl',
        };
    }
}
