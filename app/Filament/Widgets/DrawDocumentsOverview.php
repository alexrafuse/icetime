<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\DrawDocument;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class DrawDocumentsOverview extends Widget
{
    protected static string $view = 'filament.widgets.draw-documents-overview';
    protected static ?int $sort = 2;

    protected function getViewData(): array
    {
        $currentDraws = DrawDocument::query()
            ->where('valid_from', '<=', now())
            ->where(function (Builder $query) {
                $query->where('valid_until', '>=', now())
                    ->orWhereNull('valid_until');
            })
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('day_of_week');

        return [
            'days' => DrawDocument::getDayNames(),
            'currentDraws' => $currentDraws,
        ];
    }
} 