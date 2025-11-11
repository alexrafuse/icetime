<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Config\ProductMappingConfig;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

final class ImportDataCache
{
    private Collection $productsByCurlingioId;

    private Collection $productsByNormalizedName;

    private Collection $productsByPrice;

    private Collection $usersByEmail;

    private Collection $usersByCurlingioProfileId;

    private Collection $existingMemberships;

    public function __construct(
        private readonly Season $season
    ) {
        $this->loadProducts();
        $this->loadUsers();
        $this->loadMemberships();
    }

    private function loadProducts(): void
    {
        $products = Product::query()
            ->where('season_id', $this->season->id)
            ->where('is_available', true)
            ->get();

        // Index by curlingio_id
        $this->productsByCurlingioId = $products
            ->filter(fn (Product $p) => $p->curlingio_id !== null)
            ->keyBy('curlingio_id');

        // Index by normalized name
        $this->productsByNormalizedName = $products
            ->mapWithKeys(fn (Product $p) => [
                ProductMappingConfig::normalizeName($p->name) => $p,
            ]);

        // Index by price
        $this->productsByPrice = $products
            ->groupBy('price_cents')
            ->map(fn (Collection $group) => $group->first());
    }

    private function loadUsers(): void
    {
        $users = User::query()->get();

        // Index by email
        $this->usersByEmail = $users->keyBy('email');

        // Index by curlingio_profile_id
        $this->usersByCurlingioProfileId = $users
            ->filter(fn (User $u) => $u->curlingio_profile_id !== null)
            ->keyBy('curlingio_profile_id');
    }

    private function loadMemberships(): void
    {
        $memberships = UserProduct::query()
            ->where('season_id', $this->season->id)
            ->get();

        // Index by composite key: user_id|product_id|season_id
        $this->existingMemberships = $memberships->mapWithKeys(
            fn (UserProduct $up) => [
                "{$up->user_id}|{$up->product_id}|{$up->season_id}" => $up,
            ]
        );
    }

    public function findProductByCurlingioId(int $curlingioId): ?Product
    {
        return $this->productsByCurlingioId->get($curlingioId);
    }

    public function findProductByName(string $name): ?Product
    {
        return $this->productsByNormalizedName->get($name);
    }

    public function findProductByNormalizedName(string $normalizedName): ?Product
    {
        return $this->productsByNormalizedName->get($normalizedName);
    }

    public function findProductByPrice(int $priceCents): ?Product
    {
        return $this->productsByPrice->get($priceCents);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->usersByEmail->get($email);
    }

    public function findUserByCurlingioProfileId(string $curlingioProfileId): ?User
    {
        return $this->usersByCurlingioProfileId->get($curlingioProfileId);
    }

    public function findExistingMembership(int $userId, int $productId, int $seasonId): ?UserProduct
    {
        return $this->existingMemberships->get("{$userId}|{$productId}|{$seasonId}");
    }

    public function addUser(User $user): void
    {
        $this->usersByEmail->put($user->email, $user);

        if ($user->curlingio_profile_id) {
            $this->usersByCurlingioProfileId->put($user->curlingio_profile_id, $user);
        }
    }

    public function addMembership(UserProduct $membership): void
    {
        $key = "{$membership->user_id}|{$membership->product_id}|{$membership->season_id}";
        $this->existingMemberships->put($key, $membership);
    }
}
