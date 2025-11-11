<?php

namespace Database\Factories;

use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Membership\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productType = fake()->randomElement(ProductType::cases());
        $name = $this->generateName($productType);

        return [
            'season_id' => Season::factory(),
            'curlingio_id' => fake()->optional()->unique()->numberBetween(9000, 9999),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'product_type' => $productType,
            'membership_tier' => $productType === ProductType::MEMBERSHIP ? fake()->randomElement(MembershipTier::cases()) : null,
            'price_cents' => fake()->numberBetween(2000, 60000),
            'currency' => 'CAD',
            'is_available' => true,
            'metadata' => [],
        ];
    }

    private function generateName(ProductType $productType): string
    {
        return match ($productType) {
            ProductType::MEMBERSHIP => fake()->randomElement([
                'Active Membership',
                'Social Membership',
                'Student Membership',
                'Half Year Active Membership',
            ]),
            ProductType::LEAGUE => fake()->randomElement([
                'Green League Full Year',
                'Red League First Half',
                'Blue League Second Half',
            ]),
            ProductType::ADDON => fake()->randomElement([
                'Locker Rental',
                'Key Fob',
                'Equipment Storage',
            ]),
            ProductType::PROGRAM => fake()->randomElement([
                'Learn to Curl Program',
                'Junior Curling Program',
                'Advanced Skills Clinic',
            ]),
        };
    }

    public function membership(?MembershipTier $tier = null): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => ProductType::MEMBERSHIP,
            'membership_tier' => $tier ?? fake()->randomElement(MembershipTier::cases()),
            'price_cents' => fake()->numberBetween(25000, 55000),
        ]);
    }

    public function activeMembership(): static
    {
        return $this->membership(MembershipTier::ACTIVE);
    }

    public function socialMembership(): static
    {
        return $this->membership(MembershipTier::SOCIAL);
    }

    public function studentMembership(): static
    {
        return $this->membership(MembershipTier::STUDENT);
    }

    public function league(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => ProductType::LEAGUE,
            'membership_tier' => null,
            'price_cents' => fake()->numberBetween(15000, 30000),
        ]);
    }

    public function addon(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => ProductType::ADDON,
            'membership_tier' => null,
            'price_cents' => fake()->numberBetween(2000, 5000),
        ]);
    }

    public function program(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => ProductType::PROGRAM,
            'membership_tier' => null,
            'price_cents' => fake()->numberBetween(7000, 12000),
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes) => [
            'season_id' => $season->id,
        ]);
    }
}
