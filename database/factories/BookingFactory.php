<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'event_type' => $this->faker->randomElement(EventType::cases()),
            'payment_status' => $this->faker->randomElement(PaymentStatus::cases()),
            'setup_instructions' => $this->faker->sentence,
        ];
    }
} 