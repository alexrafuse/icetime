<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'recipient' => $this->faker->email,
            'type' => $this->faker->randomElement(NotificationType::cases()),
            'sent_at' => $this->faker->dateTime,
        ];
    }
} 