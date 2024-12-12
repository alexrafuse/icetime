<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    public function definition(): array
    {
        $isWeekly = $this->faker->boolean;
        
        if ($isWeekly) {
            $dayOfWeek = $this->faker->numberBetween(0, 6);
            $baseDate = Carbon::now()->startOfWeek()->addDays($dayOfWeek);
        } else {
            $dayOfWeek = null;
            $baseDate = $this->faker->dateTimeBetween('now', '+1 year');
        }

        $startTime = Carbon::parse($baseDate)->setTime(
            $this->faker->numberBetween(8, 20),
            0,
            0
        );
        
        $endTime = $startTime->copy()->addHours($this->faker->numberBetween(1, 8));

        return [
            'area_id' => Area::factory(),
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_available' => $this->faker->boolean(80),
            'note' => $this->faker->optional(0.3)->sentence,
        ];
    }

    public function weekly(): static
    {
        return $this->state(function () {
            $dayOfWeek = $this->faker->numberBetween(0, 6);
            $baseDate = Carbon::now()->startOfWeek()->addDays($dayOfWeek);
            
            $startTime = Carbon::parse($baseDate)->setTime(
                $this->faker->numberBetween(8, 20),
                0,
                0
            );
            
            return [
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addHours($this->faker->numberBetween(1, 8)),
            ];
        });
    }

    public function specificDate(Carbon $date = null): static
    {
        return $this->state(function () use ($date) {
            $baseDate = $date ?? $this->faker->dateTimeBetween('now', '+1 year');
            
            $startTime = Carbon::parse($baseDate)->setTime(
                $this->faker->numberBetween(8, 20),
                0,
                0
            );
            
            return [
                'day_of_week' => null,
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addHours($this->faker->numberBetween(1, 8)),
            ];
        });
    }

    public function forDate(Carbon $date): static
    {
        return $this->specificDate($date);
    }
} 