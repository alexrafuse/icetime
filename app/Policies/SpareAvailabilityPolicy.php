<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use Domain\Facility\Models\SpareAvailability;
use Domain\User\Models\User;

/**
 * Policy for SpareAvailability model authorization
 *
 * Custom policy with granular permissions:
 * - Anyone with VIEW_SPARES can view spares
 * - Users can create their own spare (one per user)
 * - Admins with MANAGE_SPARES can update/delete any spare
 * - Users with MANAGE_OWN_SPARE can update/delete their own spare
 */
final class SpareAvailabilityPolicy extends BasePolicy
{
    /**
     * Determine if user can view any spare availability records
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, Permission::VIEW_SPARES->value);
    }

    /**
     * Determine if user can view a specific spare availability record
     */
    public function view(User $user, mixed $spareAvailability): bool
    {
        return $this->hasPermission($user, Permission::VIEW_SPARES->value);
    }

    /**
     * Determine if user can create a spare availability record
     * Users can only create one spare availability record
     */
    public function create(User $user): bool
    {
        if (! $this->hasPermission($user, Permission::MANAGE_OWN_SPARE->value)) {
            return false;
        }

        return ! $user->spareAvailability()->exists();
    }

    /**
     * Determine if user can update a spare availability record
     */
    public function update(User $user, mixed $spareAvailability): bool
    {
        // Admins with MANAGE_SPARES can update any spare
        if ($this->hasPermission($user, Permission::MANAGE_SPARES->value)) {
            return true;
        }

        // Users can update their own spare
        return $this->hasPermission($user, Permission::MANAGE_OWN_SPARE->value)
            && $user->id === $spareAvailability->user_id;
    }

    /**
     * Determine if user can delete a spare availability record
     */
    public function delete(User $user, mixed $spareAvailability): bool
    {
        // Admins with MANAGE_SPARES can delete any spare
        if ($this->hasPermission($user, Permission::MANAGE_SPARES->value)) {
            return true;
        }

        // Users can delete their own spare
        return $this->hasPermission($user, Permission::MANAGE_OWN_SPARE->value)
            && $user->id === $spareAvailability->user_id;
    }
}
