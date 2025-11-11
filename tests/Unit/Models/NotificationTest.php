<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\NotificationType;
use Domain\Booking\Models\Booking;
use Domain\Shared\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $notification = Notification::factory()->create();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_notification_belongs_to_booking(): void
    {
        $booking = Booking::factory()->create();
        $notification = Notification::factory()->create(['booking_id' => $booking->id]);

        $this->assertTrue($notification->booking->is($booking));
    }

    public function test_notification_has_type(): void
    {
        $notification = Notification::factory()->create([
            'type' => NotificationType::CONFIRMATION,
        ]);

        $this->assertInstanceOf(NotificationType::class, $notification->type);
    }
}
