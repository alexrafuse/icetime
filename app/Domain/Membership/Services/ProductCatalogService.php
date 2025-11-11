<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use Illuminate\Support\Collection;

final class ProductCatalogService
{
    public function getAvailableProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->available()
            ->orderBy('product_type')
            ->orderBy('name')
            ->get();
    }

    public function getMembershipProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->available()
            ->memberships()
            ->orderBy('name')
            ->get();
    }

    public function getLeagueProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->available()
            ->leagues()
            ->orderBy('name')
            ->get();
    }

    public function getAddonProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->available()
            ->addons()
            ->orderBy('name')
            ->get();
    }

    public function getProgramProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->available()
            ->programs()
            ->orderBy('name')
            ->get();
    }

    public function getProductsByType(Season $season, ProductType $type): Collection
    {
        return match ($type) {
            ProductType::MEMBERSHIP => $this->getMembershipProducts($season),
            ProductType::LEAGUE => $this->getLeagueProducts($season),
            ProductType::ADDON => $this->getAddonProducts($season),
            ProductType::PROGRAM => $this->getProgramProducts($season),
        };
    }

    public function getAllProducts(Season $season): Collection
    {
        return Product::query()
            ->forSeason($season)
            ->orderBy('product_type')
            ->orderBy('name')
            ->get();
    }
}
