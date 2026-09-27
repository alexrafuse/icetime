<?php

namespace Database\Factories;

use Domain\Board\Models\Sponsor;
use Domain\Board\Models\Sponsorship;
use Domain\Board\Models\SponsorshipLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorshipFactory extends Factory
{
    protected $model = Sponsorship::class;

    public function definition(): array
    {
        return [
            'sponsor_id' => Sponsor::factory(),
            'sponsorship_level_id' => SponsorshipLevel::factory(),
            'season_id' => null,
            'amount_cents' => $this->faker->randomElement([50000, 100000, 250000, 500000]),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->dateTimeBetween('+6 months', '+1 year')->format('Y-m-d'),
            'notes' => null,
        ];
    }
}
