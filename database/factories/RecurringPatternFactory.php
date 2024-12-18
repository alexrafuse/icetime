<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Enums\FrequencyType;
use Illuminate\Support\Carbon;
use App\Models\RecurringPattern;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringPatternFactory extends Factory
{
    protected $model = RecurringPattern::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->addDays(fake()->numberBetween(1, 10));

        
        
        return [
            'title' => $this->faker->words(2, true),
            'primary_booking_id' => Booking::factory(),
            'frequency' => FrequencyType::WEEKLY,
            'interval' => fake()->numberBetween(1, 3),
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addMonths(3),
            'days_of_week' => [1, 3, 5], // Mon, Wed, Fri
            'excluded_dates' => [],
        ];
    }
}
