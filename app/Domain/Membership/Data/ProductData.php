<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ProductData extends Data
{
    public function __construct(
        public int|Optional $id,
        public int $season_id,
        public ?int $curlingio_id,
        public string $name,
        public string $slug,
        public ?string $description,
        #[WithCast(EnumCast::class)]
        public ProductType $product_type,
        #[WithCast(EnumCast::class)]
        public ?MembershipTier $membership_tier,
        public int $price_cents,
        public string $currency,
        public bool $is_available,
        public ?array $metadata,
        public Carbon|Optional $created_at,
        public Carbon|Optional $updated_at,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            season_id: $product->season_id,
            curlingio_id: $product->curlingio_id,
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            product_type: $product->product_type,
            membership_tier: $product->membership_tier,
            price_cents: $product->price_cents,
            currency: $product->currency,
            is_available: $product->is_available,
            metadata: $product->metadata,
            created_at: $product->created_at,
            updated_at: $product->updated_at,
        );
    }

    public function getFormattedPrice(): string
    {
        $dollars = $this->price_cents / 100;

        return '$'.number_format($dollars, 2);
    }
}
