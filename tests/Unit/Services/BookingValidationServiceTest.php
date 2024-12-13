<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\BookingValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingValidationService();
    }

    public function test_area_availability_checks_active_status(): void
    {
        $area = Area::factory()->create(['is_active' => false]);
        $date = Carbon::parse('2024-01-01');

        $result = $this->service->validateBooking(
            collect([$area]),
            $date,
            $date->copy()->setTime(9, 0),
            $date->copy()->setTime(10, 0)
        );

        $this->assertFalse($result);
    }

    public function test_area_availability_with_weekly_schedule(): void
    {
        $area = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01'); // Monday
        
        Availability::factory()->create([
            'area_id' => $area->id,
            'day_of_week' => $date->dayOfWeek,
            'start_time' => $date->copy()->setTime(9, 0),
            'end_time' => $date->copy()->setTime(17, 0),
            'is_available' => true,
        ]);

        // Test within available hours
        $this->assertTrue($this->service->isAreaAvailable(
            $area,
            $date,
            $date->copy()->setTime(10, 0),
            $date->copy()->setTime(11, 0)
        ));

        // Test outside available hours
        $this->assertFalse($this->service->isAreaAvailable(
            $area,
            $date,
            $date->copy()->setTime(8, 0),
            $date->copy()->setTime(9, 0)
        ));
    }

    public function test_area_availability_with_specific_date(): void
    {
        $area = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01');

        Availability::factory()->create([
            'area_id' => $area->id,
            'day_of_week' => null,
            'start_time' => $date->copy()->setTime(9, 0),
            'end_time' => $date->copy()->setTime(17, 0),
            'is_available' => true,
        ]);

        // Test within available hours
        $this->assertTrue($this->service->isAreaAvailable(
            $area,
            $date,
            $date->copy()->setTime(10, 0),
            $date->copy()->setTime(11, 0)
        ));

        // Test different date (should be false)
        $differentDate = $date->copy()->addDay();
        $this->assertFalse($this->service->isAreaAvailable(
            $area,
            $differentDate,
            $differentDate->copy()->setTime(10, 0),
            $differentDate->copy()->setTime(11, 0)
        ));
    }

    public function test_area_booking_conflicts(): void
    {
        $area = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01');

        // Create existing booking and verify it's created correctly
        $booking = Booking::factory()->create([
            'date' => $date->format('Y-m-d'),
            'start_time' => $date->copy()->setTime(10, 0)->format('H:i:s'),
            'end_time' => $date->copy()->setTime(11, 0)->format('H:i:s'),
        ]);
        
        // Verify the pivot relationship is created
        $area->bookings()->attach($booking->id);
        
        // Verify the data is in the database
        Log::debug('Test Data:', [
            'booking' => $booking->toArray(),
            'area_booking' => DB::table('area_booking')->where('booking_id', $booking->id)->get()->toArray()
        ]);

        // Test overlapping time
        $result = $this->service->isAreaBooked(
            $area,
            $date,
            $date->copy()->setTime(10, 30),
            $date->copy()->setTime(11, 30)
        );
        
        $this->assertTrue($result, 'Should detect conflict for booking from 10:30 to 11:30 when existing booking is from 10:00 to 11:00');
    }

    public function test_booking_validation_with_multiple_areas(): void
    {
        $areaA = Area::factory()->create(['is_active' => true]);
        $areaB = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01');

        // Create availability for both areas
        foreach ([$areaA, $areaB] as $area) {
            Availability::factory()->create([
                'area_id' => $area->id,
                'day_of_week' => $date->dayOfWeek,
                'start_time' => $date->copy()->setTime(9, 0),
                'end_time' => $date->copy()->setTime(17, 0),
                'is_available' => true,
            ]);
        }

        // Create booking with area A
        $booking = Booking::factory()->create([
            'date' => $date->format('Y-m-d'),
            'start_time' => $date->copy()->setTime(10, 0),
            'end_time' => $date->copy()->setTime(11, 0),
        ]);
        $booking->areas()->attach($areaA->id);

        // Test booking area A during conflict
        $this->assertTrue($this->service->isAreaBooked(
            $areaA,
            $date,
            $date->copy()->setTime(10, 30),
            $date->copy()->setTime(11, 30)
        ));

        // Test booking area A with no conflict
        $this->assertFalse($this->service->isAreaBooked(
            $areaA,
            $date,
            $date->copy()->setTime(11, 0),
            $date->copy()->setTime(12, 0)
        ));
    }

    public function test_excludes_current_booking_when_editing(): void
    {
        $area = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01');

        // Create availability
        Availability::factory()->create([
            'area_id' => $area->id,
            'day_of_week' => $date->dayOfWeek,
            'start_time' => $date->copy()->setTime(9, 0),
            'end_time' => $date->copy()->setTime(17, 0),
            'is_available' => true,
        ]);

        // Create existing booking
        $booking = Booking::factory()->create([
            'date' => $date->format('Y-m-d'),
            'start_time' => $date->copy()->setTime(10, 0),
            'end_time' => $date->copy()->setTime(11, 0),
        ]);
        $booking->areas()->attach($area->id);

        // Test that the current booking is excluded
        $this->assertFalse($this->service->isAreaBooked(
            $area,
            $date,
            $date->copy()->setTime(10, 0),
            $date->copy()->setTime(11, 0),
            $booking->id
        ));
    }

    public function test_partial_overlapping_scenarios(): void
    {
        $area = Area::factory()->create(['is_active' => true]);
        $date = Carbon::parse('2024-01-01');

        // Create existing booking
        $booking = Booking::factory()->create([
            'date' => $date->format('Y-m-d'),
            'start_time' => $date->copy()->setTime(13, 0),
            'end_time' => $date->copy()->setTime(15, 0),
        ]);
        $booking->areas()->attach($area->id);

        $overlappingScenarios = [
            // Starts before, ends during
            ['start' => 12, 'end' => 14],
            // Starts during, ends after
            ['start' => 14, 'end' => 16],
            // Completely encompasses
            ['start' => 12, 'end' => 16],
            // Completely within
            ['start' => 13, 'end' => 14],
        ];

        foreach ($overlappingScenarios as $scenario) {
            $this->assertTrue(
                $this->service->isAreaBooked(
                    $area,
                    $date,
                    $date->copy()->setTime($scenario['start'], 0),
                    $date->copy()->setTime($scenario['end'], 0)
                ),
                sprintf(
                    'Should detect conflict for booking from %d:00 to %d:00 when existing booking is from 13:00 to 15:00',
                    $scenario['start'],
                    $scenario['end']
                )
            );
        }
    }

   
}