<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\User\Models\User;

/**
 * Policy for User model authorization
 *
 * Only users with VIEW_USERS permission can view the user list.
 * Only users with MANAGE_USERS permission can manage users (create, update, delete).
 */
final class UserPolicy extends BasePolicy
{
    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, \App\Enums\Permission::VIEW_USERS->value);
    }

    /**
     * Determine if user can view a specific user
     */
    public function view(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, \App\Enums\Permission::VIEW_USERS->value);
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, \App\Enums\Permission::MANAGE_USERS->value);
    }

    /**
     * Determine if user can update users
     */
    public function update(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, \App\Enums\Permission::MANAGE_USERS->value);
    }

    /**
     * Determine if user can delete users
     */
    public function delete(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, \App\Enums\Permission::MANAGE_USERS->value);
    }
}
