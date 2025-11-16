<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\RecurrencePeriod;
use Domain\Shared\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'tally_form_url' => 'https://tally.so/r/'.$this->faker->regexify('[a-z0-9]{6}'),
            'is_active' => true,
            'priority' => $this->faker->numberBetween(1, 10),
            'starts_at' => null,
            'ends_at' => null,
            'is_recurring' => false,
            'recurrence_period' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDateRange(): self
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function recurring(RecurrencePeriod $period = RecurrencePeriod::MONTHLY): self
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_period' => $period,
        ]);
    }

    public function highPriority(): self
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }
}
