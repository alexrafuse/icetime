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
use Domain\Shared\ValueObjects\DayOfWeek;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    private const AREA_GROUPS = [
        'Ice Sheets' => [
            'Sheet A',
            'Sheet B',
            'Sheet C',
            'Sheet D',
        ],
        'Other Areas' => [
            'Lounge',
            'Kitchen',
        ],
    ];

    private const ALL_AREAS = [
        'Sheet A',
        'Sheet B',
        'Sheet C',
        'Sheet D',
        'Lounge',
        'Kitchen',
    ];

    private const LEAGUES = [
        // Daytime Drop-In
        [
            'title' => 'Mixed Drop-In',
            'days_of_week' => [
                DayOfWeek::MONDAY->value,
                DayOfWeek::TUESDAY->value,
                DayOfWeek::WEDNESDAY->value,
                DayOfWeek::THURSDAY->value,
            ],
            'start_time' => '09:30',
            'end_time' => '11:30',
            'areas' => 'all',
            'event_type' => EventType::DROP_IN,
        ],

        // Evening leagues
        [
            'title' => 'Monday Drop-In Curling',
            'days_of_week' => [DayOfWeek::MONDAY->value],
            'start_time' => '18:30',
            'end_time' => '20:00',
            'areas' => 'all',
            'event_type' => EventType::DROP_IN,
        ],
        [
            'title' => 'Tuesday Night Competitive',
            'days_of_week' => [DayOfWeek::TUESDAY->value],
            'start_time' => '18:30',
            'end_time' => '20:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Wednesday Night Social - Early',
            'days_of_week' => [DayOfWeek::WEDNESDAY->value],
            'start_time' => '18:30',
            'end_time' => '20:00',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Wednesday Night Social - Late',
            'days_of_week' => [DayOfWeek::WEDNESDAY->value],
            'start_time' => '20:15',
            'end_time' => '21:45',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Thursday Night Competitive - Early',
            'days_of_week' => [DayOfWeek::THURSDAY->value],
            'start_time' => '18:30',
            'end_time' => '20:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Thursday Night Competitive - Late',
            'days_of_week' => [DayOfWeek::THURSDAY->value],
            'start_time' => '20:30',
            'end_time' => '22:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Friday Night Social - Early',
            'days_of_week' => [DayOfWeek::FRIDAY->value],
            'start_time' => '18:30',
            'end_time' => '20:00',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Friday Night Social - Late',
            'days_of_week' => [DayOfWeek::FRIDAY->value],
            'start_time' => '20:15',
            'end_time' => '21:45',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],

        // Stick curling leagues
        [
            'title' => 'Monday Stick Team/League',
            'days_of_week' => [DayOfWeek::MONDAY->value],
            'start_time' => '13:00',
            'end_time' => '15:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Wednesday Stick Team/League',
            'days_of_week' => [DayOfWeek::WEDNESDAY->value],
            'start_time' => '13:00',
            'end_time' => '15:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],
        [
            'title' => 'Thursday Stick Social/Sign Up',
            'days_of_week' => [DayOfWeek::THURSDAY->value],
            'start_time' => '13:00',
            'end_time' => '15:30',
            'areas' => 'all',
            'event_type' => EventType::DROP_IN,
        ],
        [
            'title' => 'Friday Stick Team/League',
            'days_of_week' => [DayOfWeek::FRIDAY->value],
            'start_time' => '13:00',
            'end_time' => '15:30',
            'areas' => 'all',
            'event_type' => EventType::LEAGUE,
        ],

        // Green League
        [
            'title' => 'Green League Drop-in',
            'days_of_week' => [DayOfWeek::MONDAY->value],
            'start_time' => '19:35',
            'end_time' => '21:00',
            'areas' => 'all',
            'event_type' => EventType::DROP_IN,
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
            // Get the areas for this league
            $areaNames = $leagueData['areas'] === 'all' ? self::ALL_AREAS : $leagueData['areas'];
            $areas = Area::whereIn('name', $areaNames)->get();

            if ($areas->count() !== count($areaNames)) {
                throw new \RuntimeException("Not all areas found for {$leagueData['title']}");
            }

            // Create the primary booking
            $startDate = Carbon::parse('2025-10-10');
            $booking = Booking::create([
                'user_id' => $systemUser->id,
                'title' => $leagueData['title'],
                'date' => $startDate->copy()->next(collect($leagueData['days_of_week'])->first())->format('Y-m-d'),
                'start_time' => $leagueData['start_time'],
                'end_time' => $leagueData['end_time'],
                'event_type' => $leagueData['event_type'],
                'payment_status' => PaymentStatus::PAID,
            ]);

            // Attach areas to the booking
            $booking->areas()->attach($areas->pluck('id'));

            // Create the recurring pattern
            $pattern = RecurringPattern::create([
                'title' => $leagueData['title'],
                'frequency' => FrequencyType::WEEKLY,
                'interval' => 1,
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addYear(),
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
