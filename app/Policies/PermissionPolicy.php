<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\User\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Policy for Permission model authorization
 *
 * Only admins can manage permissions.
 * All CRUD operations require admin role.
 */
final class PermissionPolicy extends BasePolicy
{
    /**
     * All permission management operations require admin role
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, mixed $permission): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, mixed $permission): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, mixed $permission): bool
    {
        return $this->isAdmin($user);
    }
}
