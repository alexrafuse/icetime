<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\RecurringPattern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RecurringPatternFactory extends Factory
{
    protected $model = RecurringPattern::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->addDays(fake()->numberBetween(1, 10));
        
        return [
            'booking_id' => Booking::factory(),
            'frequency' => fake()->randomElement(['DAILY', 'WEEKLY', 'MONTHLY']),
            'interval' => fake()->numberBetween(1, 3),
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addMonths(3),
            'days_of_week' => [1, 3, 5], // Mon, Wed, Fri
            'excluded_dates' => [],
        ];
    }
}
