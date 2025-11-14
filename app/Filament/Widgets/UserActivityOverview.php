<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\UserActivity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class UserActivityOverview extends BaseWidget
{
    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        return auth()->user()?->can(Permission::VIEW_MEMBERSHIPS->value) ?? false;
    }

    protected function getStats(): array
    {
        $activeToday = UserActivity::query()
            ->whereDate('active_at', '>=', now()->startOfDay())
            ->distinct('user_id')
            ->count('user_id');

        $activeThisWeek = UserActivity::query()
            ->whereDate('active_at', '>=', now()->startOfWeek())
            ->distinct('user_id')
            ->count('user_id');

        $activeThisMonth = UserActivity::query()
            ->whereDate('active_at', '>=', now()->startOfMonth())
            ->distinct('user_id')
            ->count('user_id');

        $activeLastMonth = UserActivity::query()
            ->whereDate('active_at', '>=', now()->subMonth()->startOfMonth())
            ->whereDate('active_at', '<=', now()->subMonth()->endOfMonth())
            ->distinct('user_id')
            ->count('user_id');

        return [
            Stat::make('Active Users Today', $activeToday)
                ->description('Users who accessed the dashboard today')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart($this->getDailyActivityTrend()),

            Stat::make('Active This Week', $activeThisWeek)
                ->description('Unique users this week')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Active This Month', $activeThisMonth)
                ->description('Unique users this month')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('primary'),

            Stat::make('Last Month', $activeLastMonth)
                ->description('For comparison')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color($activeThisMonth >= $activeLastMonth ? 'success' : 'warning'),
        ];
    }

    protected function getDailyActivityTrend(): array
    {
        return collect(range(6, 0))
            ->map(function ($daysAgo) {
                $date = now()->subDays($daysAgo);

                return UserActivity::query()
                    ->whereDate('active_at', $date->toDateString())
                    ->distinct('user_id')
                    ->count('user_id');
            })
            ->toArray();
    }
}
