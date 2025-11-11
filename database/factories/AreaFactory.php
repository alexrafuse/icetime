<?php

namespace Database\Factories;

use Domain\Facility\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'base_price' => $this->faker->randomFloat(2, 10, 100),
            'is_active' => true,
        ];
    }
}
