<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\SpareAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpareAvailabilityFactory extends Factory
{
    protected $model = SpareAvailability::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'monday' => $this->faker->boolean(),
            'tuesday' => $this->faker->boolean(),
            'wednesday' => $this->faker->boolean(),
            'thursday' => $this->faker->boolean(),
            'friday' => $this->faker->boolean(),
            'phone_number' => $this->faker->phoneNumber(),
            'sms_enabled' => $this->faker->boolean(),
            'call_enabled' => $this->faker->boolean(),
            'notes' => $this->faker->optional()->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
} 