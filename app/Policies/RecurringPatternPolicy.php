<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\RecurringPattern;
use Illuminate\Auth\Access\HandlesAuthorization;

final class RecurringPatternPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function view(User $user, RecurringPattern $pattern): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function update(User $user, RecurringPattern $pattern): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function delete(User $user, RecurringPattern $pattern): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }
} 