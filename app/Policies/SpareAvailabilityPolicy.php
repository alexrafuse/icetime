<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\SpareAvailability;
use App\Enums\Permission;

class SpareAvailabilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::VIEW_SPARES);
    }

    public function view(User $user, SpareAvailability $spareAvailability): bool
    {
        return $user->can(Permission::VIEW_SPARES);
    }

    public function create(User $user): bool
    {
        if (!$user->can(Permission::MANAGE_OWN_SPARE)) {
            return false;
        }
        
        return !$user->spareAvailability()->exists();
    }

    public function update(User $user, SpareAvailability $spareAvailability): bool
    {
        if ($user->can(Permission::MANAGE_SPARES)) {
            return true;
        }

        return $user->can(Permission::MANAGE_OWN_SPARE) && 
               $user->id === $spareAvailability->user_id;
    }

    public function delete(User $user, SpareAvailability $spareAvailability): bool
    {
        if ($user->can(Permission::MANAGE_SPARES)) {
            return true;
        }

        return $user->can(Permission::MANAGE_OWN_SPARE) && 
               $user->id === $spareAvailability->user_id;
    }
} 