<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Carbon\Carbon;
use Domain\User\Models\User;

final class AssignProductToUserAction
{
    public function __construct(
        private readonly RecalculateMembershipStatusAction $recalculateStatus
    ) {}

    public function execute(
        User $user,
        Product $product,
        Season $season,
        ?Carbon $assignedAt = null,
        ?Carbon $expiresAt = null,
        ?string $purchaseReference = null,
        ?int $pricePaidCents = null,
        array $metadata = [],
        MembershipStatus $status = MembershipStatus::ACTIVE
    ): UserProduct {
        $userProduct = UserProduct::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'season_id' => $season->id,
            'price_paid_cents' => $pricePaidCents,
            'assigned_at' => $assignedAt ?? now(),
            'expires_at' => $expiresAt,
            'status' => $status,
            'purchase_reference' => $purchaseReference,
            'metadata' => $metadata,
        ]);

        if ($product->isMembership() && $status === MembershipStatus::ACTIVE) {
            $this->recalculateStatus->execute($user, $season);
        }

        return $userProduct->fresh(['product', 'season']);
    }
}
