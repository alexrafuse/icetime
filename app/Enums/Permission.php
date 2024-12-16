<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    // Spares Management
    case VIEW_SPARES = 'spares.view';
    case MANAGE_SPARES = 'spares.manage';
    case MANAGE_OWN_SPARE = 'spares.manage.own';

    // Booking Management
    case VIEW_BOOKINGS = 'bookings.view';
    case MANAGE_BOOKINGS = 'bookings.manage';
    case MANAGE_OWN_BOOKINGS = 'bookings.manage.own';

    // Area Management
    case VIEW_AREAS = 'areas.view';
    case MANAGE_AREAS = 'areas.manage';

    // User Management
    case VIEW_USERS = 'users.view';
    case MANAGE_USERS = 'users.manage';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            // Spares
            self::VIEW_SPARES => 'View Spares List',
            self::MANAGE_SPARES => 'Manage All Spares',
            self::MANAGE_OWN_SPARE => 'Manage Own Spare Status',
            
            // Bookings
            self::VIEW_BOOKINGS => 'View Bookings',
            self::MANAGE_BOOKINGS => 'Manage All Bookings',
            self::MANAGE_OWN_BOOKINGS => 'Manage Own Bookings',
            
            // Areas
            self::VIEW_AREAS => 'View Areas',
            self::MANAGE_AREAS => 'Manage Areas',
            
            // Users
            self::VIEW_USERS => 'View Users',
            self::MANAGE_USERS => 'Manage Users',
        };
    }

    public static function byFeature(): array
    {
        return [
            'Spares' => [
                self::VIEW_SPARES,
                self::MANAGE_SPARES,
                self::MANAGE_OWN_SPARE,
            ],
            'Bookings' => [
                self::VIEW_BOOKINGS,
                self::MANAGE_BOOKINGS,
                self::MANAGE_OWN_BOOKINGS,
            ],
            'Areas' => [
                self::VIEW_AREAS,
                self::MANAGE_AREAS,
            ],
            'Users' => [
                self::VIEW_USERS,
                self::MANAGE_USERS,
            ],
        ];
    }
} 