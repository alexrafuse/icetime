<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_created(): void
    {
        $payment = Payment::factory()->create();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
        ]);
    }

    public function test_payment_belongs_to_booking(): void
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create(['booking_id' => $booking->id]);

        $this->assertTrue($payment->booking->is($booking));
    }

    public function test_payment_has_status(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PAID,
        ]);

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
    }
} 