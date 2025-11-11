<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

final class DashboardSeparator extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-separator';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';
}
