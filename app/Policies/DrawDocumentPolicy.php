<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\DrawDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

final class DrawDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Allow all users to view the list of documents
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow all users to view individual documents
     */
    public function view(User $user, DrawDocument $drawDocument): bool
    {
        return true;
    }

    /**
     * Only staff and admins can create documents
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    /**
     * Only staff and admins can update documents
     */
    public function update(User $user, DrawDocument $drawDocument): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    /**
     * Only staff and admins can delete documents
     */
    public function delete(User $user, DrawDocument $drawDocument): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }
} 