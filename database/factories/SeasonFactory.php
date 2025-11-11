<?php

namespace Database\Factories;

use App\Domain\Membership\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Membership\Models\Season>
 */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2024, 2030);
        $endYear = $startYear + 1;
        $startDate = "{$startYear}-10-01";
        $endDate = "{$endYear}-03-31";

        return [
            'name' => "{$startYear}-{$endYear}",
            'slug' => "{$startYear}-{$endYear}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_current' => false,
            'is_registration_open' => fake()->boolean(),
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }

    public function registrationOpen(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registration_open' => true,
        ]);
    }

    public function registrationClosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registration_open' => false,
        ]);
    }
}
