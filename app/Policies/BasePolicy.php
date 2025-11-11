<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleEnum;
use Domain\User\Models\User;

/**
 * Base policy providing common authorization logic
 *
 * All model policies should extend this class to inherit
 * common permission checking methods and reduce code duplication.
 */
abstract class BasePolicy
{
    /**
     * Check if user has admin role
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }

    /**
     * Check if user has staff role
     */
    protected function isStaff(User $user): bool
    {
        return $user->hasRole(RoleEnum::STAFF->value);
    }

    /**
     * Check if user is admin or staff
     */
    protected function isAdminOrStaff(User $user): bool
    {
        return $user->hasAnyRole([RoleEnum::ADMIN->value, RoleEnum::STAFF->value]);
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        return $user->can($permission);
    }

    /**
     * Standard policy: Admin and staff can view any records
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can view individual records
     */
    public function view(User $user, mixed $model): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can create records
     */
    public function create(User $user): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can update records
     */
    public function update(User $user, mixed $model): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can delete records
     */
    public function delete(User $user, mixed $model): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can restore records
     */
    public function restore(User $user, mixed $model): bool
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Standard policy: Admin and staff can force delete records
     */
    public function forceDelete(User $user, mixed $model): bool
    {
        return $this->isAdminOrStaff($user);
    }
}
