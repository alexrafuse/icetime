<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum ResourceCategory: string
{
    case Events = 'events';
    case Curriculum = 'curriculum';
    case Schedules = 'schedules';
    case Rules = 'rules';
    case General = 'general';
    case Volunteer = 'volunteer';
    case Membership = 'membership';

    public function getLabel(): string
    {
        return match ($this) {
            self::Events => 'Events',
            self::Curriculum => 'Curriculum',
            self::Schedules => 'Schedules',
            self::Rules => 'Rules',
            self::General => 'General',
            self::Volunteer => 'Volunteer',
            self::Membership => 'Membership',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Events => 'success',
            self::Curriculum => 'primary',
            self::Schedules => 'warning',
            self::Rules => 'danger',
            self::General => 'info',
            self::Volunteer => 'warning',
            self::Membership => 'primary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Events => 'heroicon-o-calendar',
            self::Curriculum => 'heroicon-o-academic-cap',
            self::Schedules => 'heroicon-o-clock',
            self::Rules => 'heroicon-o-document-text',
            self::General => 'heroicon-o-folder',
            self::Volunteer => 'heroicon-o-hand-raised',
            self::Membership => 'heroicon-o-identification',
        };
    }
}
