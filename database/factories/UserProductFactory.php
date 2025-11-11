<?php

namespace Database\Factories;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Membership\Models\UserProduct>
 */
class UserProductFactory extends Factory
{
    protected $model = UserProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'season_id' => Season::factory(),
            'assigned_at' => now(),
            'expires_at' => null,
            'status' => MembershipStatus::ACTIVE,
            'purchase_reference' => fake()->optional()->uuid(),
            'metadata' => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::ACTIVE,
            'expires_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::EXPIRED,
            'expires_at' => now()->subDays(30),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::PENDING,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::CANCELLED,
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes) => [
            'season_id' => $season->id,
        ]);
    }

    public function withPurchaseReference(string $reference): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_reference' => $reference,
        ]);
    }
}
