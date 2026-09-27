<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case BOARD_MEMBER = 'board_member';
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
            self::BOARD_MEMBER => 'Board Member',
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
            self::BOARD_MEMBER => [
                Permission::VIEW_BOARD_MEETINGS->value,
                Permission::MANAGE_BOARD_MEETINGS->value,
                Permission::VIEW_BOARD_MINUTES->value,
                Permission::MANAGE_BOARD_MINUTES->value,
                Permission::VIEW_SPONSORS->value,
                Permission::MANAGE_SPONSORS->value,
                Permission::VIEW_DONORS->value,
                Permission::MANAGE_DONORS->value,
                Permission::VIEW_CLUB_POLICIES->value,
                Permission::MANAGE_CLUB_POLICIES->value,
                Permission::VIEW_CLUB_BYLAWS->value,
                Permission::MANAGE_CLUB_BYLAWS->value,
                Permission::VIEW_SPARES->value,
                Permission::VIEW_BOOKINGS->value,
                Permission::VIEW_AREAS->value,
                Permission::VIEW_MEMBERSHIPS->value,
                Permission::VIEW_PRODUCTS->value,
                Permission::VIEW_RESOURCES->value,
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
