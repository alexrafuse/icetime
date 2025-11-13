<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\User\Models\User;

/**
 * Policy for User model authorization
 *
 * Members can view the user list (names only).
 * Only admins and staff can manage users (create, update, delete).
 */
final class UserPolicy extends BasePolicy
{
    /**
     * All authenticated users can view the user list
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * All authenticated users can view individual users
     */
    public function view(User $user, mixed $model): bool
    {
        return true;
    }

    //
    // Management operations inherited from BasePolicy
    // which default to admin/staff access:
    // - create(): admin/staff only
    // - update(): admin/staff only
    // - delete(): admin/staff only
    // - restore(): admin/staff only
    // - forceDelete(): admin/staff only
    //
}
