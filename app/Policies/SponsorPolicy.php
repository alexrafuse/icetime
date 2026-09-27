<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use Domain\User\Models\User;

final class SponsorPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrBoardMember($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->isAdminOrBoardMember($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_SPONSORS->value)
            || $this->isAdmin($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_SPONSORS->value)
            || $this->isAdmin($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->hasPermission($user, Permission::MANAGE_SPONSORS->value)
            || $this->isAdmin($user);
    }
}
