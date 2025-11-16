<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\SurveyStatus;
use Domain\Shared\Models\Survey;
use Domain\Shared\Models\SurveyResponse;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(SurveyStatus::cases()),
            'responded_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'metadata' => null,
        ];
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => SurveyStatus::COMPLETED,
        ]);
    }

    public function notInterested(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => SurveyStatus::NOT_INTERESTED,
        ]);
    }

    public function dismissed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => SurveyStatus::DISMISSED,
        ]);
    }

    public function later(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => SurveyStatus::LATER,
        ]);
    }
}
