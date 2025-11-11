<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('slug', '2025-2026')->first();

        if (! $season) {
            $this->command->error('Season 2025-2026 not found. Please run SeasonsSeeder first.');

            return;
        }

        $products = require __DIR__.'/data/curlingio_products.php';

        foreach ($products as $productData) {
            Product::query()->create([
                'season_id' => $season->id,
                'curlingio_id' => $productData['curlingio_id'],
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => null,
                'product_type' => $productData['product_type'],
                'membership_tier' => $productData['membership_tier'],
                'capacity' => $productData['capacity'],
                'price_cents' => $productData['price'],
                'currency' => 'CAD',
                'is_available' => true,
                'metadata' => [],
            ]);
        }

        $this->command->info('Created '.count($products).' products for season '.$season->name);
    }
}
