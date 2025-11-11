<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

final class CreateCoupleFromOrderItemAction
{
    public function __construct(
        private readonly CreateUserFromProfileAction $createUserAction,
        private readonly AssignProductToUserAction $assignProductAction,
    ) {}

    /**
     * Create users for a couple membership and assign the product to both.
     *
     * @return Collection<User> Collection of both users
     */
    public function execute(
        OrderItemImportData $orderItem,
        Product $product,
        Season $season,
    ): Collection {
        if (! $orderItem->hasSecondMember()) {
            throw new \InvalidArgumentException('Order item must have a second member to create a couple');
        }

        // Create first user from primary profile
        $primaryUser = $this->createUserAction->execute($orderItem->profile);

        // Create second user from second member profile
        $secondaryUser = $this->createUserAction->execute($orderItem->second_member_profile);

        // Assign product to both users
        $purchaseReference = $orderItem->getPurchaseReference();
        $assignedAt = $orderItem->created_at;
        // Split price equally between both users for couple memberships
        $pricePerPerson = (int) round($orderItem->total_cents / 2);

        $metadata = [
            'order_id' => $orderItem->order_id,
            'item_name' => $orderItem->item_name,
            'couple_membership' => true,
            'partner_user_id' => null, // Will be set below
        ];

        // Assign to primary user
        $primaryMetadata = array_merge($metadata, ['partner_user_id' => $secondaryUser->id]);
        $this->assignProductAction->execute(
            user: $primaryUser,
            product: $product,
            season: $season,
            assignedAt: $assignedAt,
            expiresAt: $season->end_date,
            purchaseReference: $purchaseReference,
            pricePaidCents: $pricePerPerson,
            metadata: $primaryMetadata,
            status: MembershipStatus::ACTIVE,
        );

        // Assign to secondary user
        $secondaryMetadata = array_merge($metadata, ['partner_user_id' => $primaryUser->id]);
        $this->assignProductAction->execute(
            user: $secondaryUser,
            product: $product,
            season: $season,
            assignedAt: $assignedAt,
            expiresAt: $season->end_date,
            purchaseReference: $purchaseReference,
            pricePaidCents: $pricePerPerson,
            metadata: $secondaryMetadata,
            status: MembershipStatus::ACTIVE,
        );

        return collect([$primaryUser, $secondaryUser]);
    }
}
