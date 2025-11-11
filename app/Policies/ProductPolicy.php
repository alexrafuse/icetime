<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use Domain\User\Models\User;

class ProductPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, Permission::VIEW_PRODUCTS->value)
            || $this->isAdminOrStaff($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::VIEW_PRODUCTS->value)
            || $this->isAdminOrStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_PRODUCTS->value)
            || $this->isAdmin($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_PRODUCTS->value)
            || $this->isAdmin($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_PRODUCTS->value)
            || $this->isAdmin($user);
    }

    public function restore(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_PRODUCTS->value)
            || $this->isAdmin($user);
    }

    public function forceDelete(User $user, mixed $model): bool
    {
        return $this->isAdmin($user);
    }
}
