<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Actions\RecalculateMembershipStatusAction;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Season;
use Domain\User\Models\User;

final class MembershipStatusService
{
    public function __construct(
        private readonly RecalculateMembershipStatusAction $recalculateAction
    ) {}

    public function getStatus(User $user, ?Season $season = null): MembershipStatus
    {
        if ($user->current_membership_status !== null) {
            return $user->current_membership_status;
        }

        return $this->recalculateAndCache($user, $season);
    }

    public function recalculateAndCache(User $user, ?Season $season = null): MembershipStatus
    {
        return $this->recalculateAction->execute($user, $season);
    }

    public function hasActiveMembership(User $user, ?Season $season = null): bool
    {
        $status = $this->getStatus($user, $season);

        return $status === MembershipStatus::ACTIVE;
    }

    public function isPending(User $user, ?Season $season = null): bool
    {
        $status = $this->getStatus($user, $season);

        return $status === MembershipStatus::PENDING;
    }

    public function isExpired(User $user, ?Season $season = null): bool
    {
        $status = $this->getStatus($user, $season);

        return $status === MembershipStatus::EXPIRED;
    }

    public function isCancelled(User $user, ?Season $season = null): bool
    {
        $status = $this->getStatus($user, $season);

        return $status === MembershipStatus::CANCELLED;
    }
}
