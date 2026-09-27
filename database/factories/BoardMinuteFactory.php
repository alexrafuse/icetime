<?php

namespace Database\Factories;

use Domain\Board\Enums\MinuteStatus;
use Domain\Board\Models\BoardMeeting;
use Domain\Board\Models\BoardMinute;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardMinuteFactory extends Factory
{
    protected $model = BoardMinute::class;

    public function definition(): array
    {
        return [
            'board_meeting_id' => BoardMeeting::factory(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => MinuteStatus::DRAFT,
            'recorded_by_user_id' => User::factory(),
            'approved_at' => null,
            'approved_by_user_id' => null,
            'file_path' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => MinuteStatus::APPROVED,
            'approved_at' => now(),
            'approved_by_user_id' => User::factory(),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['status' => MinuteStatus::PENDING_APPROVAL]);
    }
}
