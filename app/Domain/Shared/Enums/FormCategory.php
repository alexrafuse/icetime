<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

enum FormCategory: string
{
    case REGISTRATION = 'registration';
    case MEMBERSHIP = 'membership';
    case VOLUNTEER = 'volunteer';
    case GENERAL = 'general';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::REGISTRATION => 'Registration',
            self::MEMBERSHIP => 'Membership',
            self::VOLUNTEER => 'Volunteer',
            self::GENERAL => 'General',
            self::OTHER => 'Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::REGISTRATION => 'success',
            self::MEMBERSHIP => 'primary',
            self::VOLUNTEER => 'warning',
            self::GENERAL => 'info',
            self::OTHER => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::REGISTRATION => 'heroicon-o-user-plus',
            self::MEMBERSHIP => 'heroicon-o-identification',
            self::VOLUNTEER => 'heroicon-o-hand-raised',
            self::GENERAL => 'heroicon-o-document-text',
            self::OTHER => 'heroicon-o-folder',
        };
    }
}
