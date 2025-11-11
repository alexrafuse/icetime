<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use App\Services\BookingValidationService;
use App\Services\RecurringBookingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Booking\Models\Booking;
use Domain\Booking\Models\RecurringPattern;
use Domain\Facility\Models\Area;
use Domain\Facility\Models\Availability;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Area $area;

    private BookingValidationService $validationService;

    private RecurringBookingService $recurringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create area with availability
        $this->area = Area::factory()->create(['is_active' => true]);
        Availability::factory()->weekly()->create([
            'area_id' => $this->area->id,
            'day_of_week' => 1, // Monday
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);

        $this->validationService = app(BookingValidationService::class);
        $this->recurringService = app(RecurringBookingService::class);
    }

    // ========================
    // End-to-End Booking Creation
    // ========================

    public function test_full_booking_creation_flow(): void
    {
        $bookingData = [
            'title' => 'Full Flow Booking',
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
            'setup_instructions' => 'Setup test',
        ];

        $booking = Booking::create($bookingData);
        $booking->areas()->attach($this->area->id);

        // Verify booking was created
        $this->assertDatabaseHas('bookings', [
            'title' => 'Full Flow Booking',
            'user_id' => $this->user->id,
        ]);

        // Verify relationship
        $this->assertCount(1, $booking->areas);
        $this->assertEquals($this->area->id, $booking->areas->first()->id);

        // Verify user relationship
        $this->assertEquals($this->user->id, $booking->user->id);
    }

    public function test_booking_with_multiple_areas_persists_correctly(): void
    {
        $area2 = Area::factory()->create(['is_active' => true]);
        Availability::factory()->weekly()->create([
            'area_id' => $area2->id,
            'day_of_week' => 1,
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(14, 0, 0),
            'end_time' => now()->setTime(16, 0, 0),
            'event_type' => EventType::LEAGUE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $booking->areas()->attach([$this->area->id, $area2->id]);

        $this->assertCount(2, $booking->fresh()->areas);
        $this->assertTrue($booking->areas->contains($this->area));
        $this->assertTrue($booking->areas->contains($area2));
    }

    // ========================
    // Recurring Pattern Integration
    // ========================

    public function test_recurring_bookings_creation_flow(): void
    {
        $bookingData = [
            'user_id' => $this->user->id,
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
            'setup_instructions' => null,
            'areas' => [$this->area->id],
        ];

        $patternData = [
            'frequency' => FrequencyType::WEEKLY->value,
            'interval' => 1,
            'start_date' => now()->next('Monday')->format('Y-m-d'),
            'end_date' => now()->next('Monday')->addWeeks(4)->format('Y-m-d'),
            'days_of_week' => [1], // Monday only
            'excluded_dates' => [],
        ];

        $bookings = $this->recurringService->createRecurringBookings($bookingData, $patternData);

        // Should create 5 bookings (start date + 4 weeks)
        $this->assertGreaterThanOrEqual(4, $bookings->count());

        // Verify all bookings have the pattern
        $pattern = RecurringPattern::first();
        $this->assertNotNull($pattern);

        foreach ($bookings as $booking) {
            $this->assertEquals($pattern->id, $booking->recurring_pattern_id);
        }

        // Verify primary booking
        $this->assertNotNull($pattern->primary_booking_id);
        $this->assertEquals($pattern->primary_booking_id, $bookings->first()->id);
    }

    public function test_recurring_pattern_with_multiple_days_of_week(): void
    {
        $bookingData = [
            'user_id' => $this->user->id,
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
            'areas' => [$this->area->id],
        ];

        // Add availability for Wednesday
        Availability::factory()->weekly()->create([
            'area_id' => $this->area->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);

        $patternData = [
            'frequency' => FrequencyType::WEEKLY->value,
            'interval' => 1,
            'start_date' => now()->next('Monday')->format('Y-m-d'),
            'end_date' => now()->next('Monday')->addWeeks(2)->format('Y-m-d'),
            'days_of_week' => [1, 3], // Monday and Wednesday
            'excluded_dates' => [],
        ];

        $bookings = $this->recurringService->createRecurringBookings($bookingData, $patternData);

        // Should create bookings for both Monday and Wednesday
        $this->assertGreaterThanOrEqual(4, $bookings->count()); // At least 2 weeks * 2 days
    }

    public function test_recurring_pattern_respects_excluded_dates(): void
    {
        $startDate = now()->next('Monday');
        $excludedDate = $startDate->copy()->addWeek();

        $bookingData = [
            'user_id' => $this->user->id,
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
            'areas' => [$this->area->id],
        ];

        $patternData = [
            'frequency' => FrequencyType::WEEKLY->value,
            'interval' => 1,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $startDate->copy()->addWeeks(3)->format('Y-m-d'),
            'days_of_week' => [1], // Monday only
            'excluded_dates' => [$excludedDate->format('Y-m-d')],
        ];

        $bookings = $this->recurringService->createRecurringBookings($bookingData, $patternData);

        // Verify excluded date is not in bookings
        $bookingDates = $bookings->pluck('date')->map(fn ($date) => $date->format('Y-m-d'))->toArray();
        $this->assertNotContains($excludedDate->format('Y-m-d'), $bookingDates);
    }

    public function test_can_link_booking_to_existing_pattern(): void
    {
        $pattern = RecurringPattern::factory()->create();

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
            'recurring_pattern_id' => $pattern->id,
        ]);

        $this->assertEquals($pattern->id, $booking->recurringPattern->id);
        $this->assertTrue($pattern->bookings->contains($booking));
    }

    // ========================
    // Availability Validation Integration
    // ========================

    public function test_validation_service_allows_booking_within_availability(): void
    {
        $bookingData = [
            'area_id' => $this->area->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
        ];

        $result = $this->validationService->validateBooking($bookingData);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validation_service_rejects_booking_outside_availability(): void
    {
        $bookingData = [
            'area_id' => $this->area->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(23, 0, 0), // Outside availability (8-22)
            'end_time' => now()->setTime(23, 30, 0),
        ];

        $result = $this->validationService->validateBooking($bookingData);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validation_service_detects_conflicts(): void
    {
        // Create existing booking
        $existingBooking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $existingBooking->areas()->attach($this->area->id);

        // Try to book overlapping time
        $conflictingData = [
            'area_id' => $this->area->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(11, 0, 0), // Overlaps with 10-12
            'end_time' => now()->setTime(13, 0, 0),
        ];

        $result = $this->validationService->validateBooking($conflictingData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('conflict', strtolower(implode(' ', $result['errors'])));
    }

    public function test_validation_service_allows_adjacent_bookings(): void
    {
        // Create existing booking
        $existingBooking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $existingBooking->areas()->attach($this->area->id);

        // Book immediately after (no overlap)
        $adjacentData = [
            'area_id' => $this->area->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(12, 0, 0), // Starts exactly when previous ends
            'end_time' => now()->setTime(14, 0, 0),
        ];

        $result = $this->validationService->validateBooking($adjacentData);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validation_excludes_current_booking_when_editing(): void
    {
        // Create a booking
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $booking->areas()->attach($this->area->id);

        // Try to update the same booking (should not conflict with itself)
        $updateData = [
            'area_id' => $this->area->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 30, 0), // Extended end time
            'exclude_booking_id' => $booking->id,
        ];

        $result = $this->validationService->validateBooking($updateData);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validation_checks_area_is_active(): void
    {
        $inactiveArea = Area::factory()->create(['is_active' => false]);

        $bookingData = [
            'area_id' => $inactiveArea->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
        ];

        $result = $this->validationService->validateBooking($bookingData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('inactive', strtolower(implode(' ', $result['errors'])));
    }

    // ========================
    // Complex Scenarios
    // ========================

    public function test_booking_with_payments_relationship(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Create payment for this booking
        $payment = $booking->payments()->create([
            'amount' => 100.00,
            'status' => \App\Enums\PaymentStatus::PAID,
        ]);

        $this->assertEquals($booking->id, $payment->booking_id);
        $this->assertCount(1, $booking->fresh()->payments);
    }

    public function test_booking_cascade_delete_behavior(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $booking->areas()->attach($this->area->id);

        $bookingId = $booking->id;

        // Delete booking
        $booking->delete();

        // Verify booking is deleted
        $this->assertDatabaseMissing('bookings', ['id' => $bookingId]);

        // Verify pivot entries are cleaned up
        $this->assertDatabaseMissing('area_booking', ['booking_id' => $bookingId]);
    }

    public function test_specific_date_availability_overrides_weekly(): void
    {
        // Create weekly availability (available)
        $weeklyAvailability = Availability::factory()->weekly()->create([
            'area_id' => $this->area->id,
            'day_of_week' => 1, // Monday
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);

        $specificDate = now()->next('Monday');

        // Create specific date availability (not available)
        $specificAvailability = Availability::factory()->specificDate($specificDate)->create([
            'area_id' => $this->area->id,
            'day_of_week' => null,
            'start_time' => $specificDate->copy()->setTime(8, 0, 0),
            'end_time' => $specificDate->copy()->setTime(22, 0, 0),
            'is_available' => false,
        ]);

        // Try to book on the specific date
        $bookingData = [
            'area_id' => $this->area->id,
            'date' => $specificDate->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
        ];

        $result = $this->validationService->validateBooking($bookingData);

        // Should be blocked by specific date override
        $this->assertFalse($result['success']);
    }
}
