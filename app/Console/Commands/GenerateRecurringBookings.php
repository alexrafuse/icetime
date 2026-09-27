<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecurringBookingService;
use Domain\Booking\Models\RecurringPattern;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateRecurringBookings extends Command
{
    protected $signature = 'bookings:generate-recurring {--days=30}';

    protected $description = 'Generate recurring bookings for the specified number of days ahead';

    public function handle(RecurringBookingService $bookingService): int
    {
        $daysAhead = (int) $this->option('days');
        $endDate = Carbon::now()->addDays($daysAhead);

        $patterns = RecurringPattern::query()
            ->where('end_date', '>=', Carbon::now())
            ->with(['primaryBooking' => ['areas']])
            ->get();

        $this->info("Found {$patterns->count()} active recurring patterns.");

        $bookingsCreated = $patterns
            ->filter(fn (RecurringPattern $pattern) => $pattern->booking !== null)
            ->sum(function (RecurringPattern $pattern) use ($bookingService, $endDate) {
                $originalBooking = $pattern->booking;

                $bookingData = [
                    'user_id' => $originalBooking->user_id,
                    'start_time' => $originalBooking->start_time,
                    'end_time' => $originalBooking->end_time,
                    'event_type' => $originalBooking->event_type,
                    'payment_status' => $originalBooking->payment_status,
                    'setup_instructions' => $originalBooking->setup_instructions,
                    'areas' => $originalBooking->areas->pluck('id')->toArray(),
                ];

                $patternData = [
                    'frequency' => $pattern->frequency,
                    'interval' => $pattern->interval,
                    'start_date' => Carbon::now()->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'days_of_week' => $pattern->days_of_week,
                    'excluded_dates' => $pattern->excluded_dates,
                ];

                $newBookings = $bookingService->createRecurringBookings($bookingData, $patternData);
                $this->info("Generated {$newBookings->count()} bookings for pattern ID {$pattern->id}");

                return $newBookings->count();
            });

        $this->info("Successfully created {$bookingsCreated} recurring bookings.");

        return Command::SUCCESS;
    }
}
