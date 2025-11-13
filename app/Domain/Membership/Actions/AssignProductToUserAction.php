<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use App\Domain\Membership\Services\BulkImportBuffer;
use Carbon\Carbon;
use Domain\User\Models\User;

final class AssignProductToUserAction
{
    public function __construct(
        private readonly ?RecalculateMembershipStatusAction $recalculateStatus = null,
        private readonly ?BulkImportBuffer $buffer = null,
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

        // Track affected user for bulk status recalculation at end of import
        // Note: We don't buffer membership creation - it's already created above
        // We only track the user ID for later status recalculation
        if ($this->buffer) {
            $this->buffer->trackAffectedUser($user->id);
        }

        // Skip expensive status recalculation during bulk import (will be done at end)
        if (! $this->buffer && $product->isMembership() && $status === MembershipStatus::ACTIVE && $this->recalculateStatus) {
            $this->recalculateStatus->execute($user, $season);
        }

        // Skip expensive fresh() query during bulk import
        if ($this->buffer) {
            return $userProduct;
        }

        return $userProduct->fresh(['product', 'season']);
    }
}
