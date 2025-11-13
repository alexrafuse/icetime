<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Permission;
use Filament\Widgets\Widget;

final class SpareAvailabilityPrompt extends Widget
{
    protected static string $view = 'filament.widgets.spare-availability-prompt';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can(Permission::VIEW_SPARES->value) ?? false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $hasSpareAvailability = $user?->spareAvailability()->exists() ?? false;

        return [
            'hasSpareAvailability' => $hasSpareAvailability,
        ];
    }
}
