<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicIceCalendarTest extends TestCase
{
    use RefreshDatabase;

    private const RANGE = ['start' => '2026-10-05', 'end' => '2026-10-12'];

    public function test_feed_returns_ice_bookings_in_range_with_their_sheets(): void
    {
        $sheetA = Area::factory()->create(['name' => 'Sheet A']);
        $kitchen = Area::factory()->create(['name' => 'Kitchen']);

        Booking::factory()->withAreas(collect([$sheetA, $kitchen]))->create([
            'title' => 'Tuesday Night Competitive',
            'event_type' => EventType::LEAGUE,
            'date' => '2026-10-06',
            'start_time' => '18:30:00',
            'end_time' => '20:30:00',
        ]);
        Booking::factory()->withAreas(collect([$kitchen]))->create(['date' => '2026-10-06', 'title' => 'Kitchen Only']);
        Booking::factory()->withAreas(collect([$sheetA]))->create(['date' => '2026-10-20', 'title' => 'Out Of Range']);

        $this->getJson(route('api.ice-calendar', self::RANGE))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Tuesday Night Competitive')
            ->assertJsonPath('data.0.start', '2026-10-06T18:30:00')
            ->assertJsonPath('data.0.end', '2026-10-06T20:30:00')
            ->assertJsonPath('data.0.extendedProps.sheets', ['Sheet A']);
    }

    public function test_feed_shows_learn_to_curl_by_name(): void
    {
        $sheet = Area::factory()->create(['name' => 'Sheet A']);

        Booking::factory()->withAreas(collect([$sheet]))->create([
            'title' => 'Adult Learn-to-Curl',
            'event_type' => EventType::LEARN_TO_CURL,
            'date' => '2026-10-05',
        ]);

        $this->getJson(route('api.ice-calendar', self::RANGE))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Adult Learn-to-Curl')
            ->assertJsonPath('data.0.extendedProps.event_type', 'Learn to Curl');
    }

    public function test_feed_hides_private_rental_details(): void
    {
        $sheet = Area::factory()->create(['name' => 'Sheet B']);

        Booking::factory()->withAreas(collect([$sheet]))->create([
            'title' => 'Smith Family Party',
            'event_type' => EventType::PRIVATE,
            'date' => '2026-10-07',
            'setup_instructions' => 'Gate code 1234',
        ]);

        $response = $this->getJson(route('api.ice-calendar', self::RANGE))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Private rental');

        $body = $response->getContent();
        $this->assertStringNotContainsString('Smith', $body);
        $this->assertStringNotContainsString('Gate code', $body);
        $this->assertStringNotContainsString('payment', $body);
        $this->assertStringNotContainsString('user', $body);
    }

    public function test_feed_ignores_inactive_sheets(): void
    {
        $closed = Area::factory()->create(['name' => 'Sheet D', 'is_active' => false]);

        Booking::factory()->withAreas(collect([$closed]))->create(['date' => '2026-10-07']);

        $this->getJson(route('api.ice-calendar', self::RANGE))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_feed_rejects_ranges_longer_than_the_cap(): void
    {
        $this->getJson(route('api.ice-calendar', ['start' => '2026-01-01', 'end' => '2026-12-31']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end');
    }

    public function test_embed_page_is_public(): void
    {
        $this->withoutVite();

        $this->get(route('embed.ice-calendar'))
            ->assertOk()
            ->assertSee(route('api.ice-calendar'), false)
            ->assertHeaderMissing('X-Frame-Options');
    }
}
