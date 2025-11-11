<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Filament\Widgets\WeekCalendarOverview;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeekCalendarOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->area = Area::factory()->create(['is_active' => true]);
    }

    public function test_widget_renders_successfully(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful();
    }

    public function test_widget_shows_current_week_bookings(): void
    {
        $this->actingAs($this->admin);

        // Create bookings for this week
        $mondayBooking = Booking::factory()->create([
            'title' => 'Monday Booking',
            'user_id' => $this->admin->id,
            'date' => now()->startOfWeek(),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $mondayBooking->areas()->attach($this->area);

        $fridayBooking = Booking::factory()->create([
            'title' => 'Friday Booking',
            'user_id' => $this->admin->id,
            'date' => now()->startOfWeek()->addDays(4),
            'start_time' => now()->setTime(14, 0, 0),
            'end_time' => now()->setTime(16, 0, 0),
            'event_type' => EventType::LEAGUE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $fridayBooking->areas()->attach($this->area);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee('Monday Booking')
            ->assertSee('Friday Booking');
    }

    public function test_widget_displays_all_seven_days(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(WeekCalendarOverview::class);

        // Verify all 7 days are present in the data
        $this->assertCount(7, $component->viewData('weekDays'));
    }

    public function test_widget_shows_empty_state_for_days_without_bookings(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee('No bookings');
    }

    public function test_widget_displays_booking_details(): void
    {
        $this->actingAs($this->admin);

        $booking = Booking::factory()->create([
            'title' => 'Detailed Booking',
            'user_id' => $this->admin->id,
            'date' => now()->startOfWeek(),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::TOURNAMENT,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $booking->areas()->attach($this->area);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee('Detailed Booking')
            ->assertSee($this->area->name)
            ->assertSee($this->admin->name);
    }

    public function test_widget_does_not_show_bookings_outside_current_week(): void
    {
        $this->actingAs($this->admin);

        // Create a booking for next week
        $nextWeekBooking = Booking::factory()->create([
            'title' => 'Next Week Booking',
            'user_id' => $this->admin->id,
            'date' => now()->addWeek(),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $nextWeekBooking->areas()->attach($this->area);

        // Create a booking for last week
        $lastWeekBooking = Booking::factory()->create([
            'title' => 'Last Week Booking',
            'user_id' => $this->admin->id,
            'date' => now()->subWeek(),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $lastWeekBooking->areas()->attach($this->area);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertDontSee('Next Week Booking')
            ->assertDontSee('Last Week Booking');
    }

    public function test_widget_displays_multiple_bookings_per_day(): void
    {
        $this->actingAs($this->admin);

        $date = now()->startOfWeek();

        // Create multiple bookings on the same day
        for ($i = 1; $i <= 3; $i++) {
            $booking = Booking::factory()->create([
                'title' => "Booking {$i}",
                'user_id' => $this->admin->id,
                'date' => $date,
                'start_time' => now()->setTime(8 + ($i * 2), 0, 0),
                'end_time' => now()->setTime(8 + ($i * 2) + 1, 0, 0),
                'event_type' => EventType::PRIVATE,
                'payment_status' => PaymentStatus::PAID,
            ]);
            $booking->areas()->attach($this->area);
        }

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee('Booking 1')
            ->assertSee('Booking 2')
            ->assertSee('Booking 3');
    }

    public function test_widget_shows_bookings_with_multiple_areas(): void
    {
        $this->actingAs($this->admin);

        $area2 = Area::factory()->create(['name' => 'Area Two', 'is_active' => true]);

        $booking = Booking::factory()->create([
            'title' => 'Multi-Area Booking',
            'user_id' => $this->admin->id,
            'date' => now()->startOfWeek(),
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(12, 0, 0),
            'event_type' => EventType::LEAGUE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $booking->areas()->attach([$this->area->id, $area2->id]);

        Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee($this->area->name)
            ->assertSee($area2->name);
    }

    public function test_widget_displays_different_event_types(): void
    {
        $this->actingAs($this->admin);

        $date = now()->startOfWeek();

        // Private booking
        $privateBooking = Booking::factory()->create([
            'title' => 'Private Event',
            'user_id' => $this->admin->id,
            'date' => $date,
            'start_time' => now()->setTime(8, 0, 0),
            'end_time' => now()->setTime(9, 0, 0),
            'event_type' => EventType::PRIVATE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $privateBooking->areas()->attach($this->area);

        // League booking
        $leagueBooking = Booking::factory()->create([
            'title' => 'League Event',
            'user_id' => $this->admin->id,
            'date' => $date,
            'start_time' => now()->setTime(10, 0, 0),
            'end_time' => now()->setTime(11, 0, 0),
            'event_type' => EventType::LEAGUE,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $leagueBooking->areas()->attach($this->area);

        // Tournament booking
        $tournamentBooking = Booking::factory()->create([
            'title' => 'Tournament Event',
            'user_id' => $this->admin->id,
            'date' => $date,
            'start_time' => now()->setTime(12, 0, 0),
            'end_time' => now()->setTime(13, 0, 0),
            'event_type' => EventType::TOURNAMENT,
            'payment_status' => PaymentStatus::PAID,
        ]);
        $tournamentBooking->areas()->attach($this->area);

        $component = Livewire::test(WeekCalendarOverview::class)
            ->assertSuccessful()
            ->assertSee('Private Event')
            ->assertSee('League Event')
            ->assertSee('Tournament Event');
    }

    public function test_widget_appears_on_dashboard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful()
            ->assertSeeLivewire(WeekCalendarOverview::class);
    }
}
