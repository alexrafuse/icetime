<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case MEMBER = 'member';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff Member',
            self::MEMBER => 'Club Member',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => PermissionEnum::values(), // Admin gets all permissions
            self::STAFF => [
                PermissionEnum::VIEW_SPARES,
                PermissionEnum::MANAGE_SPARES,
                PermissionEnum::VIEW_BOOKINGS,
                PermissionEnum::MANAGE_BOOKINGS,
                PermissionEnum::VIEW_AREAS,
            ],
            self::MEMBER => [
                PermissionEnum::VIEW_SPARES,
                PermissionEnum::MANAGE_OWN_SPARE,
                PermissionEnum::VIEW_BOOKINGS,
                PermissionEnum::MANAGE_OWN_BOOKINGS,
                PermissionEnum::VIEW_AREAS,
            ],
        };
    }
} 