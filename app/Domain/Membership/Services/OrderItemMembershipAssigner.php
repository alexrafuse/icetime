<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Actions\AssignProductToUserAction;
use App\Domain\Membership\Actions\CreateCoupleFromOrderItemAction;
use App\Domain\Membership\Actions\CreateUserFromProfileAction;
use App\Domain\Membership\Data\ImportStats;
use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Illuminate\Support\Facades\DB;

final class OrderItemMembershipAssigner
{
    public function __construct(
        private readonly CreateUserFromProfileAction $createUserFromProfile,
        private readonly AssignProductToUserAction $assignProductToUser,
        private readonly CreateCoupleFromOrderItemAction $createCoupleFromOrderItem,
        private readonly ?ImportDataCache $cache = null,
    ) {}

    public function assign(
        OrderItemImportData $orderItem,
        Product $product,
        Season $season,
        ImportStats $stats,
        ImportLogger $logger
    ): void {
        if ($orderItem->hasSecondMember()) {
            $this->assignCoupleMembership($orderItem, $product, $season, $stats, $logger);
        } else {
            $this->assignSingleMembership($orderItem, $product, $season, $stats, $logger);
        }
    }

    private function assignCoupleMembership(
        OrderItemImportData $orderItem,
        Product $product,
        Season $season,
        ImportStats $stats,
        ImportLogger $logger
    ): void {
        $stats->incrementCoupleMemberships();
        $logger->logCoupleMembership();

        DB::transaction(function () use ($orderItem, $product, $season, $stats, $logger) {
            $this->createCoupleFromOrderItem->execute($orderItem, $product, $season);

            $stats->incrementImportedUsers(2);
            $stats->incrementImportedMemberships(2);

            $logger->logPrimaryMember(
                $orderItem->profile->first_name,
                $orderItem->profile->last_name,
                $orderItem->profile->email
            );

            $logger->logPartnerMember(
                $orderItem->second_member_profile->first_name,
                $orderItem->second_member_profile->last_name,
                $orderItem->second_member_profile->email
            );
        });
    }

    private function assignSingleMembership(
        OrderItemImportData $orderItem,
        Product $product,
        Season $season,
        ImportStats $stats,
        ImportLogger $logger
    ): void {
        $logger->logIndividualMembership();

        DB::transaction(function () use ($orderItem, $product, $season, $stats, $logger) {
            $user = $this->createUserFromProfile->execute($orderItem->profile);

            // Check cache first if available, otherwise query database
            $existing = $this->cache
                ? $this->cache->findExistingMembership($user->id, $product->id, $season->id)
                : UserProduct::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->where('season_id', $season->id)
                    ->first();

            if ($existing) {
                $this->updateExistingMembership($existing, $orderItem, $stats, $logger);
            } else {
                $membership = $this->createNewMembership($user, $product, $season, $orderItem, $stats, $logger);

                // Add to cache for future lookups
                if ($this->cache && $membership) {
                    $this->cache->addMembership($membership);
                }
            }
        });
    }

    private function updateExistingMembership(
        UserProduct $existing,
        OrderItemImportData $orderItem,
        ImportStats $stats,
        ImportLogger $logger
    ): void {
        $existing->update([
            'status' => MembershipStatus::ACTIVE,
            'assigned_at' => $orderItem->created_at,
            'purchase_reference' => $orderItem->getPurchaseReference(),
            'price_paid_cents' => $orderItem->total_cents,
            'metadata' => array_merge($existing->metadata ?? [], [
                'order_id' => $orderItem->order_id,
                'item_name' => $orderItem->item_name,
                'updated_at' => now()->toDateTimeString(),
            ]),
        ]);

        $stats->incrementUpdatedMemberships();
        $logger->logUserUpdated(
            $orderItem->profile->first_name,
            $orderItem->profile->last_name,
            $orderItem->profile->email
        );
    }

    private function createNewMembership(
        $user,
        Product $product,
        Season $season,
        OrderItemImportData $orderItem,
        ImportStats $stats,
        ImportLogger $logger
    ): ?UserProduct {
        $membership = $this->assignProductToUser->execute(
            user: $user,
            product: $product,
            season: $season,
            assignedAt: $orderItem->created_at,
            expiresAt: $season->end_date,
            purchaseReference: $orderItem->getPurchaseReference(),
            pricePaidCents: $orderItem->total_cents,
            metadata: [
                'imported_from_curlingio' => true,
                'order_id' => $orderItem->order_id,
                'item_name' => $orderItem->item_name,
                'import_date' => now()->toDateTimeString(),
            ],
            status: MembershipStatus::ACTIVE
        );

        $stats->incrementImportedUsers();
        $stats->incrementImportedMemberships();
        $logger->logUserCreated(
            $orderItem->profile->first_name,
            $orderItem->profile->last_name,
            $orderItem->profile->email
        );

        return $membership;
    }
}
