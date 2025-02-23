<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Availability;
use Illuminate\Auth\Access\HandlesAuthorization;

final class AvailabilityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function view(User $user, Availability $availability): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function update(User $user, Availability $availability): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function delete(User $user, Availability $availability): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }
} 