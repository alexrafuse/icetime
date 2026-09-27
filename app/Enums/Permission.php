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

    // Membership Management
    case VIEW_MEMBERSHIPS = 'memberships.view';
    case MANAGE_MEMBERSHIPS = 'memberships.manage';
    case VIEW_OWN_MEMBERSHIP = 'memberships.view.own';

    // Product Management
    case VIEW_PRODUCTS = 'products.view';
    case MANAGE_PRODUCTS = 'products.manage';

    // Season Management
    case MANAGE_SEASONS = 'seasons.manage';

    // Resource Management
    case VIEW_RESOURCES = 'resources.view';
    case MANAGE_RESOURCES = 'resources.manage';

    // Board Meetings
    case VIEW_BOARD_MEETINGS = 'board.meetings.view';
    case MANAGE_BOARD_MEETINGS = 'board.meetings.manage';

    // Board Minutes
    case VIEW_BOARD_MINUTES = 'board.minutes.view';
    case MANAGE_BOARD_MINUTES = 'board.minutes.manage';

    // Sponsors
    case VIEW_SPONSORS = 'board.sponsors.view';
    case MANAGE_SPONSORS = 'board.sponsors.manage';

    // Donors
    case VIEW_DONORS = 'board.donors.view';
    case MANAGE_DONORS = 'board.donors.manage';

    // Governance
    case VIEW_CLUB_POLICIES = 'board.policies.view';
    case MANAGE_CLUB_POLICIES = 'board.policies.manage';
    case VIEW_CLUB_BYLAWS = 'board.bylaws.view';
    case MANAGE_CLUB_BYLAWS = 'board.bylaws.manage';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
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

            // Memberships
            self::VIEW_MEMBERSHIPS => 'View All Memberships',
            self::MANAGE_MEMBERSHIPS => 'Manage All Memberships',
            self::VIEW_OWN_MEMBERSHIP => 'View Own Membership',

            // Products
            self::VIEW_PRODUCTS => 'View Products',
            self::MANAGE_PRODUCTS => 'Manage Products',

            // Seasons
            self::MANAGE_SEASONS => 'Manage Seasons',

            // Resources
            self::VIEW_RESOURCES => 'View Resources',
            self::MANAGE_RESOURCES => 'Manage Resources',

            // Board Meetings
            self::VIEW_BOARD_MEETINGS => 'View Board Meetings',
            self::MANAGE_BOARD_MEETINGS => 'Manage Board Meetings',

            // Board Minutes
            self::VIEW_BOARD_MINUTES => 'View Board Minutes',
            self::MANAGE_BOARD_MINUTES => 'Manage Board Minutes',

            // Sponsors
            self::VIEW_SPONSORS => 'View Sponsors',
            self::MANAGE_SPONSORS => 'Manage Sponsors',

            // Donors
            self::VIEW_DONORS => 'View Donors',
            self::MANAGE_DONORS => 'Manage Donors',

            // Governance
            self::VIEW_CLUB_POLICIES => 'View Club Policies',
            self::MANAGE_CLUB_POLICIES => 'Manage Club Policies',
            self::VIEW_CLUB_BYLAWS => 'View Club Bylaws',
            self::MANAGE_CLUB_BYLAWS => 'Manage Club Bylaws',
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
            'Memberships' => [
                self::VIEW_MEMBERSHIPS,
                self::MANAGE_MEMBERSHIPS,
                self::VIEW_OWN_MEMBERSHIP,
            ],
            'Products' => [
                self::VIEW_PRODUCTS,
                self::MANAGE_PRODUCTS,
            ],
            'Seasons' => [
                self::MANAGE_SEASONS,
            ],
            'Resources' => [
                self::VIEW_RESOURCES,
                self::MANAGE_RESOURCES,
            ],
            'Board Meetings' => [
                self::VIEW_BOARD_MEETINGS,
                self::MANAGE_BOARD_MEETINGS,
            ],
            'Board Minutes' => [
                self::VIEW_BOARD_MINUTES,
                self::MANAGE_BOARD_MINUTES,
            ],
            'Sponsors' => [
                self::VIEW_SPONSORS,
                self::MANAGE_SPONSORS,
            ],
            'Donors' => [
                self::VIEW_DONORS,
                self::MANAGE_DONORS,
            ],
            'Governance' => [
                self::VIEW_CLUB_POLICIES,
                self::MANAGE_CLUB_POLICIES,
                self::VIEW_CLUB_BYLAWS,
                self::MANAGE_CLUB_BYLAWS,
            ],
        ];
    }
}
