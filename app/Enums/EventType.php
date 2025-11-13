<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case PRIVATE = 'private';
    case LEAGUE = 'league';
    case TOURNAMENT = 'tournament';
    case DROP_IN = 'drop_in';

    public function getColor(): string
    {
        return match ($this) {
            self::PRIVATE => 'gray',
            self::LEAGUE => 'success',
            self::TOURNAMENT => 'warning',
            self::DROP_IN => 'info',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::LEAGUE => 'League',
            self::TOURNAMENT => 'Tournament',
            self::DROP_IN => 'Drop-In',
        };
    }
}
