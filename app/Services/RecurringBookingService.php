<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FrequencyType;
use App\Models\Area;
use App\Models\Booking;
use App\Models\RecurringPattern;
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
        
        // Extract areas from booking data to prevent insertion error
        $areaIds = $bookingData['areas'];
        unset($bookingData['areas']);
        
        DB::transaction(function () use ($bookingData, $patternData, $dates, $areas, &$bookings, $areaIds) {
            // Create the first booking
            $firstDate = array_shift($dates); // Remove and get the first date
            $firstBookingData = array_merge($bookingData, [
                'date' => $firstDate->format('Y-m-d'),
            ]);
            
            if ($this->validationService->validateBooking(
                $areas,
                Carbon::parse($firstDate),
                Carbon::parse($bookingData['start_time']),
                Carbon::parse($bookingData['end_time'])
            )) {
                $firstBooking = Booking::create($firstBookingData);
                $firstBooking->areas()->attach($areaIds);
                $bookings->push($firstBooking);

                // Create the recurring pattern with the first booking as primary
                $pattern = RecurringPattern::create([
                    'user_id' => $bookingData['user_id'],
                    'frequency' => $patternData['frequency'],
                    'interval' => $patternData['interval'],
                    'start_date' => $patternData['start_date'],
                    'end_date' => $patternData['end_date'],
                    'days_of_week' => $patternData['days_of_week'] ?? null,
                    'primary_booking_id' => $firstBooking->id,
                ]);

                // Update first booking with pattern ID
                $firstBooking->update(['recurring_pattern_id' => $pattern->id]);

                // Create remaining bookings
                foreach ($dates as $date) {
                    $newBookingData = array_merge($bookingData, [
                        'date' => $date->format('Y-m-d'),
                        'recurring_pattern_id' => $pattern->id,
                    ]);
                    
                    if ($this->validationService->validateBooking(
                        $areas,
                        Carbon::parse($date),
                        Carbon::parse($bookingData['start_time']),
                        Carbon::parse($bookingData['end_time'])
                    )) {
                        $booking = Booking::create($newBookingData);
                        $booking->areas()->attach($areaIds);
                        $bookings->push($booking);
                    }
                }
            }
        });
        
        return $bookings;
    }

    public function generateDates(array $pattern): array
    {
        $startDate = Carbon::parse($pattern['start_date']);
        $endDate = Carbon::parse($pattern['end_date']);
        $dates = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($this->shouldIncludeDate($date, $pattern)) {
                $dates[] = $date->copy();
            }
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
            FrequencyType::DAILY->value => $this->isDailyMatch($date, $pattern),
            FrequencyType::WEEKLY->value => $this->isWeeklyMatch($date, $pattern),
            FrequencyType::MONTHLY->value => $this->isMonthlyMatch($date, $pattern),
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

    public function regenerateBookings(RecurringPattern $pattern): void
    {
        // Delete existing bookings except the original
        $pattern->bookings()->where('id', '!=', $pattern->booking_id)->delete();

        // Get the original booking data
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
            'start_date' => $pattern->start_date,
            'end_date' => $pattern->end_date,
            'days_of_week' => $pattern->days_of_week,
        ];

        $this->createRecurringBookings($bookingData, $patternData);
    }
}
