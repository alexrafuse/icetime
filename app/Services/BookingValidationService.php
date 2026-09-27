<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Illuminate\Support\Collection;

final class BookingValidationService
{
    public function validateBooking(
        Collection $areas,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeBookingId = null
    ): bool {
        return $areas->every(fn (Area $area) => $area->is_active
            && $this->isAreaAvailable($area, $date, $startTime, $endTime)
            && ! $this->isAreaBooked($area, $date, $startTime, $endTime, $excludeBookingId));
    }

    public function isAreaAvailable(
        Area $area,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        // Check for specific-date override first (takes priority over weekly)
        $specificDate = $area->availabilities()
            ->whereNull('day_of_week')
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->first();

        if ($specificDate) {
            if (! $specificDate->is_available) {
                return false;
            }

            return $this->isWithinTimeRange($startTime, $endTime, $specificDate);
        }

        // Fall back to weekly availability
        $weekly = $area->availabilities()
            ->where('is_available', true)
            ->whereNotNull('day_of_week')
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if (! $weekly) {
            return false;
        }

        return $this->isWithinTimeRange($startTime, $endTime, $weekly);
    }

    private function isWithinTimeRange(Carbon $startTime, Carbon $endTime, mixed $availability): bool
    {
        return $startTime->format('H:i:s') >= $availability->start_time->format('H:i:s')
            && $endTime->format('H:i:s') <= $availability->end_time->format('H:i:s');
    }

    public function isAreaBooked(
        Area $area,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $query = Booking::query()
            ->whereDate('date', $date->format('Y-m-d'))
            ->whereHas('areas', function ($query) use ($area) {
                $query->where('areas.id', $area->id);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        // Check for any overlapping bookings
        // An overlap occurs when:
        // - New booking start time is less than existing booking end time AND
        // - New booking end time is greater than existing booking start time
        return $query->where(function ($query) use ($startTime, $endTime) {
            $query->whereTime('start_time', '<', $endTime->format('H:i:s'))
                ->whereTime('end_time', '>', $startTime->format('H:i:s'));
        })->exists();
    }
}
