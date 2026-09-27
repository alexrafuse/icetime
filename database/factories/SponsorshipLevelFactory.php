<?php

namespace Database\Factories;

use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorshipLevelFactory extends Factory
{
    protected $model = SponsorshipLevel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Platinum', 'Gold', 'Silver', 'Bronze']),
            'description' => $this->faker->sentence(),
            'amount_cents' => $this->faker->randomElement([50000, 100000, 250000, 500000]),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
