<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Area;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RecurringBookingService
{
    public function __construct(
        private readonly BookingValidationService $validationService
    ) {}

    public function createRecurringBookings(array $bookingData, array $patternData): Collection
    {
        $bookings = new Collection();
        $dates = $this->generateDates($patternData);
        
        $areas = Area::findMany($bookingData['areas']);
        
        DB::transaction(function () use ($bookingData, $dates, $areas, &$bookings) {
            foreach ($dates as $date) {
                $newBookingData = array_merge($bookingData, [
                    'date' => $date->format('Y-m-d'),
                ]);
                
                if ($this->validationService->validateBooking(
                    $areas,
                    Carbon::parse($date),
                    Carbon::parse($bookingData['start_time']),
                    Carbon::parse($bookingData['end_time'])
                )) {
                    $booking = Booking::create($newBookingData);
                    $booking->areas()->attach($areas->pluck('id'));
                    $bookings->push($booking);
                }
            }
        });
        
        return $bookings;
    }

    public function generateDates(array $pattern): array
    {
        $dates = [];
        $current = Carbon::parse($pattern['start_date']);
        $end = Carbon::parse($pattern['end_date']);
        
        while ($current <= $end) {
            if ($this->shouldIncludeDate($current, $pattern)) {
                $dates[] = $current->copy();
            }
            
            $current->addDay();
        }
        
        return $dates;
    }

    private function shouldIncludeDate(Carbon $date, array $pattern): bool
    {
        // Skip excluded dates
        if (in_array($date->format('Y-m-d'), $pattern['excluded_dates'] ?? [])) {
            return false;
        }

        return match ($pattern['frequency']) {
            'DAILY' => $this->isDailyMatch($date, $pattern),
            'WEEKLY' => $this->isWeeklyMatch($date, $pattern),
            'MONTHLY' => $this->isMonthlyMatch($date, $pattern),
            default => false,
        };
    }

    private function isDailyMatch(Carbon $date, array $pattern): bool
    {
        $startDate = Carbon::parse($pattern['start_date']);
        $daysSinceStart = $date->diffInDays($startDate);
        return $daysSinceStart % ($pattern['interval'] ?? 1) === 0;
    }

    private function isWeeklyMatch(Carbon $date, array $pattern): bool
    {
        if (!isset($pattern['days_of_week'])) {
            return false;
        }

        $startDate = Carbon::parse($pattern['start_date']);
        $weeksSinceStart = $date->diffInWeeks($startDate);
        
        return $weeksSinceStart % ($pattern['interval'] ?? 1) === 0 &&
               in_array($date->dayOfWeek, $pattern['days_of_week']);
    }

    private function isMonthlyMatch(Carbon $date, array $pattern): bool
    {
        $startDate = Carbon::parse($pattern['start_date']);
        $monthsSinceStart = $date->diffInMonths($startDate);
        return $monthsSinceStart % ($pattern['interval'] ?? 1) === 0 &&
               $date->day === $startDate->day;
    }
}
