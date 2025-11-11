<?php

declare(strict_types=1);

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $season = Season::query()->where('slug', '2025-2026')->first();

        if (! $season) {
            return;
        }

        $historicalProducts = [
            [
                'name' => '2025-2026 Membership: Active (Historical Price)',
                'price_cents' => 57500,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: New Member - Active (Historical Price)',
                'price_cents' => 42500,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: One Evening League Only (Historical Price)',
                'price_cents' => 38000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::LEAGUE_ONLY,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: Active Variant (Historical Price)',
                'price_cents' => 59500,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: Social (Historical Price)',
                'price_cents' => 60000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::SOCIAL,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: Active Alt (Historical Price)',
                'price_cents' => 57000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: Active Variant 2 (Historical Price)',
                'price_cents' => 63500,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::SINGLE,
            ],
            [
                'name' => '2025-2026 Membership: Active 2 Adults (Historical Price)',
                'price_cents' => 106000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::COUPLE,
            ],
            [
                'name' => '2025-2026 Membership: Active 2 Adults Alt (Historical Price)',
                'price_cents' => 108000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::COUPLE,
            ],
            [
                'name' => '2025-2026 Membership: Active 2 Adults Variant (Historical Price)',
                'price_cents' => 97000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::COUPLE,
            ],
            [
                'name' => '2025-2026 Membership: Active 2 Adults Variant 2 (Historical Price)',
                'price_cents' => 103500,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::COUPLE,
            ],
            [
                'name' => '2025-2026 Membership: Active 2 Adults Variant 3 (Historical Price)',
                'price_cents' => 114000,
                'product_type' => ProductType::MEMBERSHIP,
                'membership_tier' => MembershipTier::ACTIVE,
                'capacity' => MembershipCapacity::COUPLE,
            ],
            [
                'name' => '2025-2026 Miscellaneous Fee (Historical Price)',
                'price_cents' => 4500,
                'product_type' => ProductType::ADDON,
                'membership_tier' => null,
                'capacity' => MembershipCapacity::SINGLE,
            ],
        ];

        foreach ($historicalProducts as $productData) {
            Product::query()->create([
                'season_id' => $season->id,
                'curlingio_id' => null,
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => 'Historical price point for legacy order imports',
                'product_type' => $productData['product_type'],
                'membership_tier' => $productData['membership_tier'],
                'capacity' => $productData['capacity'],
                'price_cents' => $productData['price_cents'],
                'currency' => 'CAD',
                'is_available' => false,
                'metadata' => ['historical' => true],
            ]);
        }
    }

    public function down(): void
    {
        $season = Season::query()->where('slug', '2025-2026')->first();

        if (! $season) {
            return;
        }

        Product::query()
            ->where('season_id', $season->id)
            ->where('is_available', false)
            ->whereJsonContains('metadata->historical', true)
            ->delete();
    }
};
