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

        $hasActiveMembership = $user->userProducts()
            ->forSeason($season)
            ->active()
            ->whereHas('product', function ($query) {
                $query->memberships()
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->exists();

        if ($hasActiveMembership) {
            $status = MembershipStatus::ACTIVE;
        } else {
            $hasPendingMembership = $user->userProducts()
                ->forSeason($season)
                ->where('status', MembershipStatus::PENDING)
                ->whereHas('product', function ($query) {
                    $query->memberships();
                })
                ->exists();

            if ($hasPendingMembership) {
                $status = MembershipStatus::PENDING;
            } else {
                $hasExpiredMembership = $user->userProducts()
                    ->forSeason($season)
                    ->whereHas('product', function ($query) {
                        $query->memberships();
                    })
                    ->exists();

                $status = $hasExpiredMembership ? MembershipStatus::EXPIRED : MembershipStatus::CANCELLED;
            }
        }

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
            ->chunk(100, function ($users) use ($season) {
                foreach ($users as $user) {
                    $this->execute($user, $season);
                }
            });
    }
}
