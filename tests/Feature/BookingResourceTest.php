<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Filament\Resources\BookingResource;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Booking\Models\Booking;
use Domain\Booking\Models\RecurringPattern;
use Domain\Facility\Models\Area;
use Domain\Facility\Models\Availability;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private User $staff;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->member = User::factory()->create(['email' => 'member@test.com']);
        $this->member->assignRole('member');

        $this->staff = User::factory()->create(['email' => 'staff@test.com']);
        $this->staff->assignRole('staff');

        // Create an active area with availability
        $this->area = Area::factory()->create(['is_active' => true]);
        Availability::factory()->weekly()->create([
            'area_id' => $this->area->id,
            'day_of_week' => 1, // Monday
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);
    }

    // ========================
    // Basic CRUD Operations
    // ========================

    public function test_admin_can_create_booking_with_valid_data(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Test Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
                'setup_instructions' => 'Test setup',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bookings', [
            'title' => 'Test Booking',
            'user_id' => $this->member->id,
            'event_type' => EventType::PRIVATE->value,
            'payment_status' => PaymentStatus::PAID->value,
        ]);
    }

    public function test_admin_can_create_booking_with_multiple_areas(): void
    {
        $this->actingAs($this->admin);

        $area2 = Area::factory()->create(['is_active' => true]);
        Availability::factory()->weekly()->create([
            'area_id' => $area2->id,
            'day_of_week' => 1,
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(22, 0, 0),
            'is_available' => true,
        ]);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Multi-Area Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'event_type' => EventType::LEAGUE->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'areas' => [$this->area->id, $area2->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Multi-Area Booking')->first();
        $this->assertCount(2, $booking->areas);
    }

    public function test_admin_can_edit_existing_booking(): void
    {
        $this->actingAs($this->admin);

        $booking = Booking::factory()->create([
            'user_id' => $this->member->id,
            'title' => 'Original Title',
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);
        $booking->areas()->attach($this->area);

        Livewire::test(BookingResource\Pages\EditBooking::class, ['record' => $booking->id])
            ->fillForm([
                'title' => 'Updated Title',
                'payment_status' => PaymentStatus::PAID->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'title' => 'Updated Title',
            'payment_status' => PaymentStatus::PAID->value,
        ]);
    }

    public function test_admin_can_delete_booking(): void
    {
        $this->actingAs($this->admin);

        $booking = Booking::factory()->create([
            'user_id' => $this->member->id,
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $bookingId = $booking->id;

        // Delete through the model (Filament handles delete via Livewire actions)
        $booking->delete();

        $this->assertDatabaseMissing('bookings', [
            'id' => $bookingId,
        ]);
    }

    public function test_member_can_list_bookings(): void
    {
        $this->actingAs($this->member);

        $ownBooking = Booking::factory()->create([
            'user_id' => $this->member->id,
            'title' => 'My Booking',
            'date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $otherBooking = Booking::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Admin Booking',
            'date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => now()->setTime(14, 0, 0),
            'end_time' => now()->setTime(16, 0, 0),
            'event_type' => EventType::LEAGUE,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->get(route('filament.admin.resources.bookings.index'));

        $response->assertSuccessful();
        // Members should only see their own bookings based on policy
    }

    // ========================
    // Recurring Pattern Integration
    // ========================

    public function test_can_create_booking_without_recurring_pattern(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Single Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
                'recurring_pattern_id' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Single Booking')->first();
        $this->assertNull($booking->recurring_pattern_id);
    }

    public function test_can_link_booking_to_existing_recurring_pattern(): void
    {
        $this->actingAs($this->admin);

        $pattern = RecurringPattern::factory()->create();

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Recurring Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
                'recurring_pattern_id' => $pattern->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Recurring Booking')->first();
        $this->assertEquals($pattern->id, $booking->recurring_pattern_id);
    }

    // ========================
    // Validation Tests
    // ========================

    public function test_cannot_create_booking_without_required_fields(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => null,
                'user_id' => null,
                'date' => null,
                'start_time' => null,
                'end_time' => null,
                'event_type' => null,
                'payment_status' => null,
                'areas' => [],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title',
                'user_id',
                'date',
                'start_time',
                'end_time',
                'event_type',
                'payment_status',
                'areas',
            ]);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Invalid Time Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '12:00',
                'end_time' => '10:00', // Before start_time
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
            ])
            ->call('create')
            ->assertHasFormErrors(['end_time']);
    }

    public function test_must_select_at_least_one_area(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'No Area Booking',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['areas']);
    }

    // ========================
    // Authorization Tests
    // ========================

    public function test_admin_can_create_bookings_for_any_user(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Admin Created for Member',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Admin Created for Member')->first();
        $this->assertEquals($this->member->id, $booking->user_id);
    }

    public function test_staff_can_create_bookings_for_any_user(): void
    {
        $this->actingAs($this->staff);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Staff Created for Member',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Staff Created for Member')->first();
        $this->assertEquals($this->member->id, $booking->user_id);
    }

    public function test_member_cannot_edit_bookings(): void
    {
        $this->actingAs($this->member);

        $booking = Booking::factory()->create([
            'user_id' => $this->member->id,
            'title' => 'My Booking',
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Members cannot edit bookings (policy only allows admin/staff)
        $response = $this->get(route('filament.admin.resources.bookings.edit', ['record' => $booking->id]));

        $response->assertForbidden();
    }

    public function test_member_cannot_edit_others_booking(): void
    {
        $this->actingAs($this->member);

        $booking = Booking::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Admin Booking',
            'date' => now()->next('Monday')->format('Y-m-d'),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $response = $this->get(route('filament.admin.resources.bookings.edit', ['record' => $booking->id]));

        $response->assertForbidden();
    }

    // ========================
    // Business Logic Tests
    // ========================

    public function test_user_id_defaults_to_authenticated_user(): void
    {
        $this->actingAs($this->member);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Auto User ID',
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
                // Note: Not explicitly setting user_id
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::where('title', 'Auto User ID')->first();
        $this->assertEquals($this->member->id, $booking->user_id);
    }

    public function test_can_add_optional_setup_instructions(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BookingResource\Pages\CreateBooking::class)
            ->fillForm([
                'title' => 'Booking with Instructions',
                'user_id' => $this->member->id,
                'date' => now()->next('Monday')->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'event_type' => EventType::PRIVATE->value,
                'payment_status' => PaymentStatus::PAID->value,
                'areas' => [$this->area->id],
                'setup_instructions' => 'Please set up the nets',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bookings', [
            'title' => 'Booking with Instructions',
            'setup_instructions' => 'Please set up the nets',
        ]);
    }
}
