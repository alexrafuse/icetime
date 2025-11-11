<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;

final class MatchProductAction
{
    public function execute(int $priceCents, Season $season): ?Product
    {
        return Product::query()
            ->where('season_id', $season->id)
            ->where('price_cents', $priceCents)
            ->first();
    }

    public function executeByCurlingioId(string $curlingioId, Season $season): ?Product
    {
        return Product::query()
            ->where('season_id', $season->id)
            ->where('curlingio_id', $curlingioId)
            ->where('is_available', true)
            ->first();
    }
}
