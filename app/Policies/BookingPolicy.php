<?php

declare(strict_types=1);

namespace App\Policies;

/**
 * Policy for Booking model authorization
 *
 * Uses standard admin/staff permissions from BasePolicy.
 * All CRUD operations require admin or staff role.
 */
final class BookingPolicy extends BasePolicy
{
    // All methods inherited from BasePolicy
    // Admin and staff can perform all operations
}
