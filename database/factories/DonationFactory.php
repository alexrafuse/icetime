<?php

namespace Database\Factories;

use Domain\Board\Enums\DonationType;
use Domain\Board\Models\Donation;
use Domain\Board\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'donor_id' => Donor::factory(),
            'type' => DonationType::MONETARY,
            'amount_cents' => $this->faker->numberBetween(1000, 100000),
            'description' => $this->faker->sentence(),
            'donated_at' => $this->faker->date(),
            'season_id' => null,
            'receipt_number' => $this->faker->numerify('REC-####'),
            'is_tax_receipted' => $this->faker->boolean(),
        ];
    }

    public function inKind(): static
    {
        return $this->state(fn () => [
            'type' => DonationType::IN_KIND,
            'amount_cents' => null,
        ]);
    }
}
