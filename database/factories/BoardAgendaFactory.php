<?php

namespace Database\Factories;

use Domain\Board\Models\BoardAgenda;
use Domain\Board\Models\BoardMeeting;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardAgendaFactory extends Factory
{
    protected $model = BoardAgenda::class;

    public function definition(): array
    {
        return [
            'board_meeting_id' => BoardMeeting::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'duration_minutes' => $this->faker->randomElement([5, 10, 15, 30]),
            'presenter_user_id' => User::factory(),
        ];
    }
}
