<?php

namespace Database\Factories;

use Domain\Board\Enums\MeetingStatus;
use Domain\Board\Models\BoardMeeting;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardMeetingFactory extends Factory
{
    protected $model = BoardMeeting::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'scheduled_at' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'location' => $this->faker->randomElement(['Board Room', 'Lounge', 'Virtual']),
            'status' => MeetingStatus::SCHEDULED,
            'called_by_user_id' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => MeetingStatus::COMPLETED,
            'scheduled_at' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => MeetingStatus::CANCELLED]);
    }
}
