<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Shared\Models\Resource;
use Domain\User\Models\User;

/**
 * Policy for Resource model authorization
 *
 * Custom policy where all users can view resources,
 * but only admin/staff can create, update, or delete them.
 */
final class ResourcePolicy extends BasePolicy
{
    /**
     * Allow all users to view the list of resources
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow all users to view individual resources
     */
    public function view(User $user, Resource $resource): bool
    {
        return true;
    }

    // create, update, delete inherited from BasePolicy (admin/staff only)
}
