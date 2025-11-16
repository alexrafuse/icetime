<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\FormCategory;
use Domain\Shared\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'tally_form_url' => 'https://tally.so/r/'.$this->faker->regexify('[a-z0-9]{6}'),
            'category' => $this->faker->randomElement(FormCategory::cases()),
            'priority' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function category(FormCategory $category): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
