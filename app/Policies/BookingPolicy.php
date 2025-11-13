<?php

declare(strict_types=1);

namespace App\Policies;

/**
 * Policy for Booking model authorization
 *
 * Only admin/staff can view and manage bookings.
 * All CRUD operations require admin or staff role.
 */
final class BookingPolicy extends BasePolicy
{
    // All methods inherited from BasePolicy
    // Admin and staff can perform all operations
    // Members cannot access bookings resource
}
