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

        Product::query()->create([
            'season_id' => $season->id,
            'curlingio_id' => null,
            'name' => '2025-2026 Membership: Couple Package (Historical Price)',
            'slug' => Str::slug('2025-2026 Membership: Couple Package (Historical Price)'),
            'description' => 'Historical price point for legacy order imports',
            'product_type' => ProductType::MEMBERSHIP,
            'membership_tier' => MembershipTier::ACTIVE,
            'capacity' => MembershipCapacity::COUPLE,
            'price_cents' => 80000,
            'currency' => 'CAD',
            'is_available' => false,
            'metadata' => ['historical' => true],
        ]);
    }

    public function down(): void
    {
        $season = Season::query()->where('slug', '2025-2026')->first();

        if (! $season) {
            return;
        }

        Product::query()
            ->where('season_id', $season->id)
            ->where('price_cents', 80000)
            ->whereJsonContains('metadata->historical', true)
            ->delete();
    }
};
