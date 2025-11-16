<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum SurveyStatus: string
{
    case COMPLETED = 'completed';
    case NOT_INTERESTED = 'not_interested';
    case DISMISSED = 'dismissed';
    case LATER = 'later';

    public function getLabel(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::NOT_INTERESTED => 'Not Interested',
            self::DISMISSED => 'Dismissed',
            self::LATER => 'Remind Me Later',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::NOT_INTERESTED => 'gray',
            self::DISMISSED => 'warning',
            self::LATER => 'info',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::COMPLETED => 'User has completed the survey',
            self::NOT_INTERESTED => 'User is not interested in this survey',
            self::DISMISSED => 'User dismissed the survey',
            self::LATER => 'User wants to be reminded later',
        };
    }
}
