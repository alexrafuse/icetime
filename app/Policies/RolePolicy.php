<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\User\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Policy for Role model authorization
 *
 * Only admins can manage roles.
 * All CRUD operations require admin role.
 */
final class RolePolicy extends BasePolicy
{
    /**
     * All role management operations require admin role
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, mixed $role): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, mixed $role): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, mixed $role): bool
    {
        return $this->isAdmin($user);
    }
}
