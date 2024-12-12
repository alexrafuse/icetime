<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Area;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingValidationService
{
    public function validateBooking(
        Collection $areas,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeBookingId = null
    ): bool {
        // First check if each area is available (active and within availability windows)
        foreach ($areas as $area) {
            if (!$area->is_active || !$this->isAreaAvailable($area, $date, $startTime, $endTime)) {
                return false;
            }
        }

        // Then check if any of the requested areas have booking conflicts
        return !$this->isAreaBooked($areas, $date, $startTime, $endTime, $excludeBookingId);
    }

    public function isAreaAvailable(
        Area $area,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime
    ): bool {
        // Create full datetime objects for comparison
        $requestedStart = $date->copy()->setTimeFrom($startTime);
        $requestedEnd = $date->copy()->setTimeFrom($endTime);

        // Check specific date availability first, then weekly availability
        $availability = $area->availabilities()
            ->where('is_available', true)
            ->where(function ($query) use ($requestedStart) {
                $query->where(function ($q) use ($requestedStart) {
                    // Specific date availability
                    $q->whereNull('day_of_week')
                      ->whereDate('start_time', $requestedStart->format('Y-m-d'));
                })->orWhere(function ($q) use ($requestedStart) {
                    // Weekly availability
                    $q->whereNotNull('day_of_week')
                      ->where('day_of_week', $requestedStart->dayOfWeek);
                });
            })
            ->first();

        if (!$availability) {
            return false;
        }

        // Check if requested time is within available hours
        return $requestedStart->format('H:i:s') >= $availability->start_time->format('H:i:s') &&
               $requestedEnd->format('H:i:s') <= $availability->end_time->format('H:i:s');
    }

    public function isAreaBooked(
        Collection $areas,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $requestedStart = $date->copy()->setTimeFrom($startTime);
        $requestedEnd = $date->copy()->setTimeFrom($endTime);

        return Booking::query()
            ->whereDate('date', $date->format('Y-m-d'))
            ->where(function ($q) use ($requestedStart, $requestedEnd) {
                $q->where(function ($inner) use ($requestedStart, $requestedEnd) {
                    $inner->where('start_time', '<', $requestedEnd)
                          ->where('end_time', '>', $requestedStart);
                });
            })
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->whereHas('areas', function ($q) use ($areas) {
                $q->whereIn('areas.id', $areas->pluck('id'));
            })
            ->exists();
    }
} 