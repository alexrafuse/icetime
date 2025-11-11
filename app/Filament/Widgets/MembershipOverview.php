<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use App\Enums\Permission;
use Domain\User\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class MembershipOverview extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        if (! auth()->user()->can(Permission::VIEW_MEMBERSHIPS->value)) {
            return [];
        }

        $currentSeason = Season::query()->where('is_current', true)->first();

        if (! $currentSeason) {
            return [
                Stat::make('No Current Season', 'Please create a season')
                    ->description('Create a season to see membership stats')
                    ->color('warning'),
            ];
        }

        $totalActiveMembers = User::query()
            ->where('current_membership_status', MembershipStatus::ACTIVE)
            ->count();

        $totalRevenue = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->with('product')
            ->get()
            ->sum(function ($userProduct) {
                $gross = $userProduct->price_paid_cents ?? $userProduct->product->price_cents;
                $refund = $userProduct->refund_amount_cents ?? 0;

                return $gross - $refund;
            });

        $newMembersThisMonth = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->whereHas('product', function ($query) {
                $query->memberships();
            })
            ->where('assigned_at', '>=', now()->startOfMonth())
            ->count();

        // Members by Tier
        $membershipsByTier = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->whereHas('product', function ($query) {
                $query->memberships();
            })
            ->with('product')
            ->get()
            ->groupBy(fn ($userProduct) => $userProduct->product->membership_tier?->value ?? 'none')
            ->map->count();

        $highestTierCount = $membershipsByTier->max() ?? 0;
        $topTier = $membershipsByTier->sortDesc()->keys()->first();
        $topTierLabel = $topTier && $topTier !== 'none'
            ? MembershipTier::from($topTier)->getLabel()
            : 'None';

        $tierBreakdown = collect(MembershipTier::cases())
            ->map(fn ($tier) => "{$tier->getLabel()}: ".($membershipsByTier[$tier->value] ?? 0))
            ->join(', ');

        // Members by Product Type
        $membersByProductType = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->with('product')
            ->get()
            ->groupBy(fn ($userProduct) => $userProduct->product->product_type?->value ?? 'none')
            ->map->count();

        $totalProducts = $membersByProductType->sum();
        $productTypeBreakdown = collect(ProductType::cases())
            ->map(fn ($type) => "{$type->getLabel()}: ".($membersByProductType[$type->value] ?? 0))
            ->join(', ');

        // Members by Capacity
        $membersByCapacity = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->whereHas('product', function ($query) {
                $query->memberships();
            })
            ->with('product')
            ->get()
            ->groupBy(fn ($userProduct) => $userProduct->product->capacity?->value ?? 'single')
            ->map->count();

        $singleCount = $membersByCapacity[MembershipCapacity::SINGLE->value] ?? 0;
        $coupleCount = $membersByCapacity[MembershipCapacity::COUPLE->value] ?? 0;
        $totalMembers = $singleCount + ($coupleCount * 2);

        // Revenue Analytics
        $totalRefunds = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->whereNotNull('refund_amount_cents')
            ->sum('refund_amount_cents');

        $grossRevenue = UserProduct::query()
            ->where('season_id', $currentSeason->id)
            ->where('status', MembershipStatus::ACTIVE)
            ->with('product')
            ->get()
            ->sum(fn ($userProduct) => $userProduct->price_paid_cents ?? $userProduct->product->price_cents);

        $avgTransaction = $totalActiveMembers > 0 ? $totalRevenue / $totalActiveMembers : 0;

        // Member Retention
        $lastSeason = Season::query()
            ->where('start_date', '<', $currentSeason->start_date)
            ->orderBy('start_date', 'desc')
            ->first();

        $returningMembers = 0;
        $newMembers = 0;

        if ($lastSeason) {
            $currentSeasonUserIds = UserProduct::query()
                ->where('season_id', $currentSeason->id)
                ->where('status', MembershipStatus::ACTIVE)
                ->whereHas('product', function ($query) {
                    $query->memberships();
                })
                ->distinct('user_id')
                ->pluck('user_id');

            $lastSeasonUserIds = UserProduct::query()
                ->where('season_id', $lastSeason->id)
                ->where('status', MembershipStatus::ACTIVE)
                ->whereHas('product', function ($query) {
                    $query->memberships();
                })
                ->distinct('user_id')
                ->pluck('user_id');

            $returningMembers = $currentSeasonUserIds->intersect($lastSeasonUserIds)->count();
            $newMembers = $currentSeasonUserIds->diff($lastSeasonUserIds)->count();
        }

        $retentionRate = $lastSeason && $lastSeasonUserIds->count() > 0
            ? round(($returningMembers / $lastSeasonUserIds->count()) * 100)
            : 0;

        return [
            Stat::make('Active Members', $totalActiveMembers)
                ->description($currentSeason->name.' season')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart($this->getActiveMembersTrend()),

            Stat::make('Season Revenue', '$'.number_format($totalRevenue / 100, 2))
                ->description('From active memberships & products')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('New This Month', $newMembersThisMonth)
                ->description('New members in '.now()->format('F'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Members by Tier', $membershipsByTier->sum())
                ->description($tierBreakdown ?: 'No active memberships')
                ->descriptionIcon('heroicon-o-chart-pie')
                ->color('info'),

            Stat::make('Members by Product Type', $totalProducts)
                ->description($productTypeBreakdown)
                ->descriptionIcon('heroicon-o-cube')
                ->color('primary'),

            Stat::make('Members by Capacity', "{$singleCount} + {$coupleCount} couple")
                ->description("Total: {$totalMembers} individual members")
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),

            Stat::make('Revenue Analytics', '$'.number_format($totalRevenue / 100, 2))
                ->description('Avg: $'.number_format($avgTransaction / 100, 2).' | Refunds: $'.number_format($totalRefunds / 100, 2))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Member Retention', $retentionRate.'%')
                ->description("Returning: {$returningMembers} | New: {$newMembers}")
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color($retentionRate >= 75 ? 'success' : ($retentionRate >= 50 ? 'warning' : 'danger')),
        ];
    }

    protected function getActiveMembersTrend(): array
    {
        $currentSeason = Season::query()->where('is_current', true)->first();

        if (! $currentSeason) {
            return [];
        }

        return collect(range(6, 0))
            ->map(function ($monthsAgo) use ($currentSeason) {
                $date = now()->subMonths($monthsAgo);

                return UserProduct::query()
                    ->where('season_id', $currentSeason->id)
                    ->where('status', MembershipStatus::ACTIVE)
                    ->whereHas('product', function ($query) {
                        $query->memberships();
                    })
                    ->where('assigned_at', '<=', $date->endOfMonth())
                    ->distinct('user_id')
                    ->count('user_id');
            })
            ->toArray();
    }
}
