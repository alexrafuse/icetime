<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Season;
use Domain\User\Models\User;

final class RecalculateMembershipStatusAction
{
    public function execute(User $user, ?Season $season = null): MembershipStatus
    {
        $season = $season ?? Season::query()->where('is_current', true)->first();

        if (! $season) {
            $status = MembershipStatus::EXPIRED;
            $user->update(['current_membership_status' => $status]);

            return $status;
        }

        $status = $this->determineStatus($user, $season);
        $user->update(['current_membership_status' => $status]);

        return $status;
    }

    public function executeForAllUsers(?Season $season = null): void
    {
        $season = $season ?? Season::query()->where('is_current', true)->first();

        if (! $season) {
            return;
        }

        User::query()
            ->with(['userProducts' => function ($query) use ($season) {
                $query->forSeason($season)->with('product');
            }])
            ->chunk(100, fn ($users) => $users->each(fn (User $user) => $this->execute($user, $season)));
    }

    private function determineStatus(User $user, Season $season): MembershipStatus
    {
        $membershipProducts = fn () => $user->userProducts()->forSeason($season)->whereHas('product', fn ($q) => $q->memberships());

        $hasActive = (clone $membershipProducts)()
            ->active()
            ->whereHas('product', fn ($q) => $q->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->exists();

        if ($hasActive) {
            return MembershipStatus::ACTIVE;
        }

        if ((clone $membershipProducts)()->where('status', MembershipStatus::PENDING)->exists()) {
            return MembershipStatus::PENDING;
        }

        if ((clone $membershipProducts)()->exists()) {
            return MembershipStatus::EXPIRED;
        }

        return MembershipStatus::CANCELLED;
    }
}
