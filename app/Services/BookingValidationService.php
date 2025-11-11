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
        // Check if each area is available (active and within availability windows)
        foreach ($areas as $area) {
            if (! $area->is_active || ! $this->isAreaAvailable($area, $date, $startTime, $endTime)) {
                return false;
            }

            // Check if the area has any booking conflicts
            if ($this->isAreaBooked($area, $date, $startTime, $endTime, $excludeBookingId)) {
                return false;
            }
        }

        return true;
    }

    public function isAreaAvailable(
        Area $area,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        $availability = $area->availabilities()
            ->where('is_available', true)
            ->where(function ($query) use ($date) {
                $query->where(function ($q) use ($date) {
                    // Specific date availability
                    $q->whereNull('day_of_week')
                        ->whereDate('start_time', $date->format('Y-m-d'));
                })->orWhere(function ($q) use ($date) {
                    // Weekly availability
                    $q->whereNotNull('day_of_week')
                        ->where('day_of_week', $date->dayOfWeek);
                });
            })
            ->first();

        if (! $availability) {
            return false;
        }

        // Compare only the time portions
        $requestedStartTime = $startTime->format('H:i:s');
        $requestedEndTime = $endTime->format('H:i:s');
        $availableStartTime = $availability->start_time->format('H:i:s');
        $availableEndTime = $availability->end_time->format('H:i:s');

        return $requestedStartTime >= $availableStartTime &&
               $requestedEndTime <= $availableEndTime;
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
