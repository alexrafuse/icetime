<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use App\Services\RecurringBookingService;
use Carbon\Carbon;
use Domain\Booking\Models\Booking;
use Domain\Booking\Models\RecurringPattern;
use Domain\Facility\Models\Area;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    private const LEAGUES = [
        [
            'title' => 'Tuesday Night Competitive',
            'days_of_week' => [2], // Tuesday
            'start_time' => '18:30',
            'end_time' => '20:30',
            'sheets' => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'],
        ],
        [
            'title' => 'Wednesday Night Social',
            'days_of_week' => [3], // Wednesday
            'start_time' => '18:00',
            'end_time' => '21:00',
            'sheets' => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'],
        ],
        [
            'title' => 'Thursday Night Competitive',
            'days_of_week' => [4], // Thursday
            'start_time' => '18:30',
            'end_time' => '20:30',
            'sheets' => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'],
        ],
        [
            'title' => 'Friday Night Social',
            'days_of_week' => [5], // Friday
            'start_time' => '18:00',
            'end_time' => '21:00',
            'sheets' => ['Sheet A', 'Sheet B', 'Sheet C', 'Sheet D'],
        ],

        [
            'title' => 'Daytime Drop-In',
            'days_of_week' => [1, 2, 3, 4, 5],
            'start_time' => '10:00',
            'end_time' => '12:30',
            'sheets' => ['Sheet A', 'Sheet B', 'Sheet C'],
        ],
    ];

    public function run(): void
    {
        $systemUser = User::first();

        if (! $systemUser) {
            throw new \RuntimeException('System user not found');
        }

        $service = app(RecurringBookingService::class);

        foreach (self::LEAGUES as $leagueData) {
            // Get the areas (sheets) for this league
            $areas = Area::whereIn('name', $leagueData['sheets'])->get();

            if ($areas->count() !== count($leagueData['sheets'])) {
                throw new \RuntimeException("Not all sheets found for {$leagueData['title']}");
            }

            // Create the primary booking
            $booking = Booking::create([
                'user_id' => $systemUser->id,
                'title' => $leagueData['title'],
                'date' => Carbon::now()->next(collect($leagueData['days_of_week'])->first())->format('Y-m-d'),
                'start_time' => $leagueData['start_time'],
                'end_time' => $leagueData['end_time'],
                'event_type' => EventType::LEAGUE,
                'payment_status' => PaymentStatus::PAID,
            ]);

            // Attach areas to the booking
            $booking->areas()->attach($areas->pluck('id'));

            // Create the recurring pattern
            $pattern = RecurringPattern::create([
                'title' => $leagueData['title'],
                'frequency' => FrequencyType::WEEKLY,
                'interval' => 1,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYear(),
                'days_of_week' => $leagueData['days_of_week'],
                'excluded_dates' => [],
                'primary_booking_id' => $booking->id,
            ]);

            // Update the booking with the pattern ID
            $booking->update(['recurring_pattern_id' => $pattern->id]);

            // Generate the recurring bookings
            $service->regenerateBookings($pattern);
        }
    }
}
