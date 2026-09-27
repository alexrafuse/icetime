<?php

declare(strict_types=1);

namespace Domain\Board\Services;

use Domain\Board\Models\ClubBylaw;
use Domain\Board\Models\ClubPolicy;
use Domain\User\Models\User;

final class DocumentService
{
    public function publishPolicy(ClubPolicy $policy, User $user): void
    {
        $policy->parent?->archive();
        $policy->publish($user);
    }

    public function publishBylaw(ClubBylaw $bylaw, User $user): void
    {
        $bylaw->parent?->archive();
        $bylaw->publish($user);
    }

    public function createPolicyRevision(ClubPolicy $policy): ClubPolicy
    {
        return $policy->createRevision();
    }

    public function createBylawRevision(ClubBylaw $bylaw): ClubBylaw
    {
        return $bylaw->createRevision();
    }

    public function archivePolicy(ClubPolicy $policy): void
    {
        $policy->archive();
    }

    public function archiveBylaw(ClubBylaw $bylaw): void
    {
        $bylaw->archive();
    }
}
