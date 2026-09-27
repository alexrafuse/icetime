<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Membership\Models\Season;
use Domain\Board\Models\Sponsorship;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SponsorshipOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentSeason = Season::query()->where('is_current', true)->first();

        if (! $currentSeason) {
            return [
                Stat::make('No Current Season', 'N/A')
                    ->description('Create a season to see sponsorship stats')
                    ->color('warning'),
            ];
        }

        $currentSponsorships = Sponsorship::query()->forSeason($currentSeason->id);

        $totalRevenue = (clone $currentSponsorships)->sum('amount_cents');
        $activeSponsors = (clone $currentSponsorships)->distinct('sponsor_id')->count('sponsor_id');
        $confirmedCount = (clone $currentSponsorships)->where('is_confirmed', true)->count();
        $paidCount = (clone $currentSponsorships)->where('is_paid', true)->count();
        $totalCount = (clone $currentSponsorships)->count();

        $levelBreakdown = Sponsorship::query()
            ->forSeason($currentSeason->id)
            ->with('level')
            ->get()
            ->groupBy(fn (Sponsorship $s) => $s->level?->name ?? 'Unknown')
            ->map->count();

        $levelDescription = $levelBreakdown
            ->map(fn (int $count, string $name) => "{$name}: {$count}")
            ->join(', ') ?: 'None';

        $seasonTrend = Season::query()
            ->orderBy('start_date', 'desc')
            ->limit(7)
            ->get()
            ->reverse()
            ->map(fn (Season $season) => (int) Sponsorship::query()->forSeason($season->id)->sum('amount_cents'))
            ->values()
            ->toArray();

        return [
            Stat::make('Sponsorship Revenue', '$'.number_format($totalRevenue / 100, 2))
                ->description($currentSeason->name)
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($seasonTrend),

            Stat::make('Active Sponsors', $activeSponsors)
                ->description("{$confirmedCount}/{$totalCount} confirmed, {$paidCount}/{$totalCount} paid")
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('By Level', $totalCount)
                ->description($levelDescription)
                ->descriptionIcon('heroicon-o-star')
                ->color('info'),
        ];
    }
}
