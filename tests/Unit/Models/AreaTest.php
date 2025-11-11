<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_can_be_created(): void
    {
        $area = Area::factory()->create();

        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
        ]);
    }

    public function test_area_can_have_bookings(): void
    {
        $area = Area::factory()->create();
        $booking = Booking::factory()->create();

        $area->bookings()->attach($booking);

        $this->assertTrue($area->bookings->contains($booking));
    }
}
