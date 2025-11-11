<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Shared\Models\DrawDocument;
use Domain\User\Models\User;

/**
 * Policy for DrawDocument model authorization
 *
 * Custom policy where all users can view documents,
 * but only admin/staff can create, update, or delete them.
 */
final class DrawDocumentPolicy extends BasePolicy
{
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
    public function view(User $user, mixed $drawDocument): bool
    {
        return true;
    }

    // create, update, delete inherited from BasePolicy (admin/staff only)
}
