<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FrequencyType;
use Domain\Booking\Models\Booking;
use Domain\Booking\Models\RecurringPattern;
use Domain\Facility\Models\Area;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class RecurringBookingService
{
    public function __construct(
        private readonly BookingValidationService $validationService
    ) {}

    public function createRecurringBookings(array $bookingData, array $patternData): Collection
    {

        // Extract and prepare data
        $areaIds = $bookingData['areas'];
        $areas = Area::findMany($areaIds);

        unset($bookingData['areas']);

        $dates = collect($this->generateDates($patternData));

        return DB::transaction(function () use ($bookingData, $patternData, $dates, $areas, $areaIds) {
            // Create primary booking and pattern
            $firstDate = $dates->shift();
            $primaryBooking = $this->createPrimaryBooking($firstDate, $bookingData, $areas, $areaIds);

            if (! $primaryBooking) {
                return collect();
            }

            $pattern = $this->createRecurringPattern($patternData, $bookingData['user_id'], $primaryBooking->id);
            $primaryBooking->update(['recurring_pattern_id' => $pattern->id]);

            // Create subsequent bookings
            $subsequentBookings = $this->createSubsequentBookings($dates, $bookingData, $areas, $areaIds, $pattern->id);

            return collect([$primaryBooking])->merge($subsequentBookings);
        });
    }

    private function createPrimaryBooking(Carbon $date, array $bookingData, Collection $areas, array $areaIds): ?Booking
    {
        $bookingData = array_merge($bookingData, [
            'date' => $date->format('Y-m-d'),
        ]);

        if (! $this->isBookingValid($areas, $date, $bookingData)) {
            return null;
        }

        unset($bookingData['areas']);

        $booking = Booking::create($bookingData);
        $booking->areas()->attach($areaIds);

        return $booking;
    }

    private function createRecurringPattern(array $patternData, int $userId, int $primaryBookingId): RecurringPattern
    {
        return RecurringPattern::create([
            'title' => $patternData['title'],
            'frequency' => $patternData['frequency'],
            'interval' => $patternData['interval'],
            'start_date' => $patternData['start_date'],
            'end_date' => $patternData['end_date'],
            'days_of_week' => $patternData['days_of_week'] ?? null,
            'primary_booking_id' => $primaryBookingId,
        ]);
    }

    private function createSubsequentBookings(Collection $dates, array $bookingData, Collection $areas, array $areaIds, int $patternId): Collection
    {
        return $dates
            ->map(function ($date) use ($bookingData, $areas, $areaIds, $patternId) {
                if (! $this->isBookingValid($areas, $date, $bookingData)) {
                    return null;
                }

                $newBookingData = array_merge($bookingData, [
                    'date' => $date->format('Y-m-d'),
                    'recurring_pattern_id' => $patternId,
                ]);

                DB::beginTransaction();
                try {

                    unset($newBookingData['areas']);

                    $booking = Booking::create($newBookingData);

                    // Ensure booking was created and has an ID
                    if (! $booking || ! $booking->id) {
                        throw new \Exception('Failed to create booking');
                    }

                    // Attach areas using sync instead of attach
                    $booking->areas()->sync($areaIds);

                    DB::commit();

                    return $booking;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to create subsequent booking', [
                        'error' => $e->getMessage(),
                        'booking_data' => $newBookingData,
                        'area_ids' => $areaIds,
                    ]);

                    return null;
                }
            })
            ->filter();
    }

    private function isBookingValid(Collection $areas, Carbon $date, array $bookingData): bool
    {
        return $this->validationService->validateBooking(
            $areas,
            Carbon::parse($date),
            Carbon::parse($bookingData['start_time']),
            Carbon::parse($bookingData['end_time'])
        );
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
        if (! isset($pattern['days_of_week'])) {
            return false;
        }

        // Use start of week to ensure all days in the same calendar week are treated consistently
        $startDate = Carbon::parse($pattern['start_date'])->startOfWeek();
        $currentWeekStart = $date->copy()->startOfWeek();
        $weeksSinceStart = $currentWeekStart->diffInWeeks($startDate);

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
        DB::transaction(function () use ($pattern) {
            try {
                // Delete existing bookings except the primary booking
                $pattern->bookings()
                    ->where('id', '!=', $pattern->primary_booking_id)
                    ->delete();

                // Get the primary booking data
                $primaryBooking = $pattern->primaryBooking;

                if (! $primaryBooking) {
                    throw new \Exception('Primary booking not found');
                }

                Log::debug('Primary booking found:', [
                    'id' => $primaryBooking->id,
                    'date' => $primaryBooking->date,
                ]);

                $bookingData = [
                    'title' => $primaryBooking->title,
                    'user_id' => $primaryBooking->user_id,
                    'start_time' => $primaryBooking->start_time,
                    'end_time' => $primaryBooking->end_time,
                    'event_type' => $primaryBooking->event_type,
                    'payment_status' => $primaryBooking->payment_status,
                    'setup_instructions' => $primaryBooking->setup_instructions,
                    'areas' => $primaryBooking->areas->pluck('id')->toArray(),
                    'date' => $primaryBooking->date,
                ];

                $patternData = [
                    'title' => $pattern->title,
                    'frequency' => $pattern->frequency->value,
                    'interval' => $pattern->interval,
                    'start_date' => $pattern->start_date,
                    'end_date' => $pattern->end_date,
                    'days_of_week' => $pattern->days_of_week,
                    'excluded_dates' => $pattern->excluded_dates ?? [],
                ];

                Log::debug('Pattern data:', $patternData);

                // Generate dates
                $allDates = collect($this->generateDates($patternData));
                Log::debug('All generated dates:', ['dates' => $allDates->map(fn ($date) => $date->format('Y-m-d'))->toArray()]);

                // Filter out primary booking date
                $dates = $allDates->filter(fn ($date) => $date->format('Y-m-d') !== Carbon::parse($primaryBooking->date)->format('Y-m-d'));
                Log::debug('Filtered dates for new bookings:', ['dates' => $dates->map(fn ($date) => $date->format('Y-m-d'))->toArray()]);

                // Create subsequent bookings
                $newBookings = $this->createSubsequentBookings(
                    $dates,
                    $bookingData,
                    $primaryBooking->areas,
                    $bookingData['areas'],
                    $pattern->id
                );

                Log::debug('Created new bookings:', [
                    'count' => $newBookings->count(),
                    'booking_ids' => $newBookings->pluck('id')->toArray(),
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to regenerate bookings', [
                    'pattern_id' => $pattern->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }
}
