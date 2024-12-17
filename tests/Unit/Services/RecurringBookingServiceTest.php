<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Area;
use App\Models\User;
use App\Models\Booking;
use App\Enums\EventType;
use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use App\Models\Availability;
use Illuminate\Support\Carbon;
use App\Services\RecurringBookingService;
use App\Services\BookingValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecurringBookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecurringBookingService $service;
    private BookingValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = app(BookingValidationService::class);
        $this->service = new RecurringBookingService($this->validationService);
    }

    public function test_can_generate_daily_recurring_dates(): void
    {
        $pattern = [
            'frequency' => FrequencyType::DAILY->value,
            'interval' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-04-07',
            'excluded_dates' => [],
        ];

        $dates = $this->service->generateDates($pattern);
        
        $this->assertCount(7, $dates);
        $this->assertEquals('2024-04-01', $dates[0]->format('Y-m-d'));
        $this->assertEquals('2024-04-07', end($dates)->format('Y-m-d'));
    }

    public function test_can_generate_weekly_recurring_dates(): void
    {
        $pattern = [
            'frequency' => FrequencyType::WEEKLY->value,
            'interval' => 1,
            'start_date' => '2024-04-01', // Monday
            'end_date' => '2024-04-30',
            'days_of_week' => [1, 3, 5], // Mon, Wed, Fri
            'excluded_dates' => [],
        ];

        $dates = $this->service->generateDates($pattern);
        
        $this->assertCount(13, $dates); // 13 occurrences of Mon/Wed/Fri in April 2024
        foreach ($dates as $date) {
            $this->assertTrue(in_array($date->dayOfWeek, [1, 3, 5]));
        }
    }

    public function test_can_create_recurring_bookings(): void
    {
        $user = User::factory()->create();
        $area = Area::factory()->create(['is_active' => true]);

        // Create availability for Mondays
        Availability::create([
            'area_id' => $area->id,
            'day_of_week' => 1, // Monday
            'start_time' => '08:00:00',
            'end_time' => '22:00:00',
            'is_available' => true,
        ]);

        $bookingData = [
            'user_id' => $user->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
            'areas' => [$area->id],
        ];

        $patternData = [
            'frequency' => FrequencyType::WEEKLY->value,
            'interval' => 1,
            'start_date' => '2024-04-01', // Monday
            'end_date' => '2024-04-15',
            'days_of_week' => [1], // Mondays only
            'excluded_dates' => [],
        ];

        $bookings = $this->service->createRecurringBookings($bookingData, $patternData);
        
        $this->assertCount(3, $bookings); // 3 Mondays in the date range
        
        // Check primary booking
        $primaryBooking = $bookings->first();
        $this->assertNotNull($primaryBooking->recurring_pattern_id);
        
        // Check recurring pattern
        $pattern = $primaryBooking->recurringPattern;
        $this->assertNotNull($pattern);
        $this->assertEquals($primaryBooking->id, $pattern->primary_booking_id);
        
        // Check all bookings
        foreach ($bookings as $booking) {
            $this->assertTrue(Carbon::parse($booking->date)->isDayOfWeek(1));
            $this->assertTrue($booking->areas->contains($area->id));
            $this->assertEquals('10:00:00', $booking->start_time->format('H:i:s'));
            $this->assertEquals('11:00:00', $booking->end_time->format('H:i:s'));
            $this->assertEquals($pattern->id, $booking->recurring_pattern_id);
        }
    }

    public function test_excludes_dates_properly(): void
    {
        $pattern = [
            'frequency' => FrequencyType::DAILY->value,
            'interval' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-04-07',
            'excluded_dates' => ['2024-04-03', '2024-04-04'],
        ];

        $dates = $this->service->generateDates($pattern);
        
        $this->assertCount(5, $dates);
        foreach ($dates as $date) {
            $this->assertNotContains($date->format('Y-m-d'), $pattern['excluded_dates']);
        }
    }
}
