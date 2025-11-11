<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Config\ProductMappingConfig;
use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Services\ImportDataCache;
use Illuminate\Support\Facades\Log;

final class MatchProductFromOrderItemAction
{
    public function __construct(
        private readonly ?ImportDataCache $cache = null,
    ) {}

    /**
     * Match a product from an order item using multiple strategies.
     *
     * Strategy order:
     * 1. Match by curlingio_id from name mapping
     * 2. Match by normalized item name (fuzzy)
     * 3. Match by price (fallback)
     */
    public function execute(OrderItemImportData $orderItem): ?Product
    {
        if (! $this->cache) {
            throw new \RuntimeException('ImportDataCache is required for product matching during import');
        }

        // Strategy 1: Try to get curlingio_id from name mapping and match by that
        $curlingioId = ProductMappingConfig::getCurlingioIdForName($orderItem->item_name);
        if ($curlingioId) {
            $product = $this->cache->findProductByCurlingioId($curlingioId);
            if ($product) {
                Log::info('Matched product by curlingio_id from mapping', [
                    'order_item_name' => $orderItem->item_name,
                    'curlingio_id' => $curlingioId,
                    'product_id' => $product->id,
                ]);

                return $product;
            }
        }

        // Strategy 2: Try normalized name match (fuzzy)
        $normalizedItemName = ProductMappingConfig::normalizeName($orderItem->item_name);
        $product = $this->cache->findProductByNormalizedName($normalizedItemName);

        if ($product) {
            Log::info('Matched product by normalized name', [
                'order_item_name' => $orderItem->item_name,
                'normalized_name' => $normalizedItemName,
                'product_id' => $product->id,
            ]);

            return $product;
        }

        // Strategy 3: Fallback to price match
        $product = $this->cache->findProductByPrice($orderItem->total_cents);

        if ($product) {
            Log::info('Matched product by price (fallback)', [
                'order_item_name' => $orderItem->item_name,
                'price_cents' => $orderItem->total_cents,
                'product_id' => $product->id,
            ]);

            return $product;
        }

        // No match found
        Log::warning('Could not match product for order item', [
            'order_id' => $orderItem->order_id,
            'item_name' => $orderItem->item_name,
            'price_cents' => $orderItem->total_cents,
        ]);

        return null;
    }
}
