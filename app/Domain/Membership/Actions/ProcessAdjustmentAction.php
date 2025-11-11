<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Domain\User\Models\User;

final class ProcessAdjustmentAction
{
    /**
     * Process an adjustment (refund/cancellation) from curling.io import.
     *
     * Finds the related user_product by order_id and applies the refund.
     */
    public function execute(
        OrderItemImportData $adjustment,
        Season $season
    ): ?UserProduct {
        // Find user by email
        $user = User::query()->where('email', $adjustment->user_email)->first();

        if (! $user) {
            return null;
        }

        // Find the user_product for this order
        // Adjustments reference the same order_id as the original purchase
        $purchaseReference = $adjustment->getPurchaseReference();

        $userProduct = UserProduct::query()
            ->where('user_id', $user->id)
            ->where('season_id', $season->id)
            ->where('purchase_reference', $purchaseReference)
            ->first();

        if (! $userProduct) {
            return null;
        }

        // Apply the refund (adjustment amounts are typically negative)
        $refundAmount = abs($adjustment->total_cents);

        $userProduct->update([
            'refund_amount_cents' => $refundAmount,
            'refund_reason' => $adjustment->item_name,
            'refunded_at' => $adjustment->created_at,
        ]);

        return $userProduct;
    }
}
