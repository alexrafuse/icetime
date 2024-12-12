<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Area;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_weekly_availability(): void
    {
        $area = Area::factory()->create();
        
        $availability = Availability::factory()
            ->for($area)
            ->weekly()
            ->create();

        $this->assertNotNull($availability->day_of_week);
        $this->assertEquals($area->id, $availability->area_id);
    }

    public function test_can_create_specific_date_availability(): void
    {
        $area = Area::factory()->create();
        $date = Carbon::parse('2024-01-01');
        
        $availability = Availability::factory()
            ->for($area)
            ->specificDate($date)
            ->create();

        $this->assertNull($availability->day_of_week);
        $this->assertEquals($date->format('Y-m-d'), $availability->start_time->format('Y-m-d'));
        $this->assertEquals($area->id, $availability->area_id);
    }

    public function test_cannot_create_duplicate_weekly_availability_for_same_area(): void
    {
        $area = Area::factory()->create();
        $dayOfWeek = 1; // Monday

        // Create first availability
        Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => $dayOfWeek,
                'start_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(15),
                'end_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(17),
            ])
            ->create();

        // Attempt to create duplicate
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('An availability for this area and time period already exists.');
        
        Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => $dayOfWeek,
                'start_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(9),
                'end_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(10),
            ])
            ->create();
    }

    public function test_can_create_same_weekly_availability_for_different_areas(): void
    {
        $area1 = Area::factory()->create();
        $area2 = Area::factory()->create();
        $dayOfWeek = 1; // Monday

        // Create first availability
        Availability::factory()
            ->for($area1)
            ->state([
                'day_of_week' => $dayOfWeek,
                'start_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(9),
                'end_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(10),
                'is_available' => false,
            ])
            ->create();

        // Create second availability for different area
        $availability2 = Availability::factory()
            ->for($area2)
            ->state([
                'day_of_week' => $dayOfWeek,
                'start_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(9),
                'end_time' => Carbon::now()->startOfWeek()->addDays($dayOfWeek)->setHour(10),
            ])
            ->create();

        $this->assertDatabaseCount('availabilities', 2);
        $this->assertEquals($area2->id, $availability2->area_id);
    }

    public function test_cannot_create_duplicate_specific_date_availability_for_same_area(): void
    {
        $area = Area::factory()->create();
        $date = Carbon::parse('2024-01-01');

        // Create first availability
        Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => null,
                'start_time' => $date->copy()->setHour(15),
                'end_time' => $date->copy()->setHour(21),
            ])
            ->create();

        // Attempt to create duplicate
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('An availability for this area and time period already exists.');
        
        Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => null,
                'start_time' => $date->copy()->setHour(9),
                'end_time' => $date->copy()->setHour(12),
            ])
            ->create();
    }

    public function test_area_can_have_both_weekly_and_specific_date_availability(): void
    {
        $area = Area::factory()->create();
        $date = Carbon::parse('2024-01-01'); // A Monday
        
        // Create weekly Monday availability
        Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => 1,
                'start_time' => $date->copy()->setHour(13),
                'end_time' => $date->copy()->setHour(18),
            ])
            ->create();

        // Create specific date that falls on a Monday
        $availability = Availability::factory()
            ->for($area)
            ->state([
                'day_of_week' => null,
                'start_time' => $date->copy()->setHour(9),
                'end_time' => $date->copy()->setHour(12),
            ])
            ->create();

        $this->assertDatabaseCount('availabilities', 2);
        $this->assertEquals($area->id, $availability->area_id);
    }
} 