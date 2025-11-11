<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\UserProduct;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserMembershipData extends Data
{
    public function __construct(
        public int|Optional $id,
        public int $user_id,
        public int $product_id,
        public int $season_id,
        public Carbon $assigned_at,
        public ?Carbon $expires_at,
        #[WithCast(EnumCast::class)]
        public MembershipStatus $status,
        public ?string $purchase_reference,
        public ?array $metadata,
        public Carbon|Optional $created_at,
        public Carbon|Optional $updated_at,
        public ProductData|Optional $product,
        public SeasonData|Optional $season,
    ) {}

    public static function fromModel(UserProduct $userProduct): self
    {
        return new self(
            id: $userProduct->id,
            user_id: $userProduct->user_id,
            product_id: $userProduct->product_id,
            season_id: $userProduct->season_id,
            assigned_at: $userProduct->assigned_at,
            expires_at: $userProduct->expires_at,
            status: $userProduct->status,
            purchase_reference: $userProduct->purchase_reference,
            metadata: $userProduct->metadata,
            created_at: $userProduct->created_at,
            updated_at: $userProduct->updated_at,
            product: $userProduct->relationLoaded('product')
                ? ProductData::from($userProduct->product)
                : Optional::create(),
            season: $userProduct->relationLoaded('season')
                ? SeasonData::from($userProduct->season)
                : Optional::create(),
        );
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::ACTIVE && ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
