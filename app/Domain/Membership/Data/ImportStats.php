<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use Spatie\LaravelData\Data;

class ImportStats extends Data
{
    public function __construct(
        public int $total_items = 0,
        public int $skipped_adjustments = 0,
        public int $skipped_no_product_match = 0,
        public int $imported_users = 0,
        public int $imported_memberships = 0,
        public int $updated_memberships = 0,
        public int $couple_memberships = 0,
        public array $warnings = [],
        public array $unmatched_products = [],
        public ?string $log_file_path = null,
    ) {}

    public function incrementTotalItems(): void
    {
        $this->total_items++;
    }

    public function incrementSkippedAdjustments(): void
    {
        $this->skipped_adjustments++;
    }

    public function incrementSkippedNoProductMatch(): void
    {
        $this->skipped_no_product_match++;
    }

    public function incrementImportedUsers(int $count = 1): void
    {
        $this->imported_users += $count;
    }

    public function incrementImportedMemberships(int $count = 1): void
    {
        $this->imported_memberships += $count;
    }

    public function incrementUpdatedMemberships(): void
    {
        $this->updated_memberships++;
    }

    public function incrementCoupleMemberships(): void
    {
        $this->couple_memberships++;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function addUnmatchedProduct(OrderItemImportData $orderItem): void
    {
        $itemKey = $orderItem->item_name.'|'.$orderItem->total_cents;

        if (! isset($this->unmatched_products[$itemKey])) {
            $this->unmatched_products[$itemKey] = [
                'item_name' => $orderItem->item_name,
                'price_cents' => $orderItem->total_cents,
                'price_display' => '$'.number_format($orderItem->total_cents / 100, 2),
                'count' => 0,
                'order_ids' => [],
            ];
        }

        $this->unmatched_products[$itemKey]['count']++;
        $this->unmatched_products[$itemKey]['order_ids'][] = $orderItem->order_id;
    }
}
