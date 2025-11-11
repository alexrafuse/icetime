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
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff Member',
            self::MEMBER => 'Club Member',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => Permission::values(), // Admin gets all permissions
            self::STAFF => [
                Permission::VIEW_SPARES->value,
                Permission::MANAGE_SPARES->value,
                Permission::VIEW_BOOKINGS->value,
                Permission::MANAGE_BOOKINGS->value,
                Permission::VIEW_AREAS->value,
                Permission::VIEW_MEMBERSHIPS->value,
                Permission::VIEW_PRODUCTS->value,
            ],
            self::MEMBER => [
                Permission::VIEW_SPARES->value,
                Permission::MANAGE_OWN_SPARE->value,
                Permission::VIEW_BOOKINGS->value,
                Permission::MANAGE_OWN_BOOKINGS->value,
                Permission::VIEW_AREAS->value,
                Permission::VIEW_OWN_MEMBERSHIP->value,
                Permission::VIEW_PRODUCTS->value,
            ],
        };
    }
}
