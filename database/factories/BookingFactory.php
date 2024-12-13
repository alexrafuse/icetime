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
        $startHour = $this->faker->numberBetween(8, 20);
        $duration = $this->faker->numberBetween(1, 4);
            
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + $duration),
            'event_type' => $this->faker->randomElement(EventType::cases()),
            'payment_status' => $this->faker->randomElement(PaymentStatus::cases()),
            'setup_instructions' => $this->faker->sentence,
        ];
    }
} 