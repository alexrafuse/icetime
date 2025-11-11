<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\EventType;
use Carbon\Carbon;
use Domain\Booking\Models\Booking;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

final class WeekCalendarOverview extends Widget
{
    protected static string $view = 'filament.widgets.week-calendar-overview';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $bookings = Booking::query()
            ->with(['areas', 'user'])
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group bookings by day
        $bookingsByDay = $bookings->groupBy(function (Booking $booking) {
            return $booking->date->format('Y-m-d');
        });

        // Create array of all 7 days with their bookings
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');

            $weekDays[] = [
                'date' => $date,
                'dayName' => $date->format('l'),
                'dayNumber' => $date->format('j'),
                'monthName' => $date->format('M'),
                'isToday' => $date->isToday(),
                'bookings' => $this->formatBookings($bookingsByDay->get($dateKey, collect())),
            ];
        }

        return [
            'weekDays' => $weekDays,
        ];
    }

    private function formatBookings(Collection $bookings): array
    {
        return $bookings->map(function (Booking $booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->title,
                'start_time' => Carbon::parse($booking->start_time)->format('g:i A'),
                'end_time' => Carbon::parse($booking->end_time)->format('g:i A'),
                'areas' => $booking->areas->pluck('name')->join(', '),
                'user_name' => $booking->user->name ?? 'N/A',
                'event_type' => $booking->event_type,
                'color' => $this->getEventColor($booking->event_type),
            ];
        })->toArray();
    }

    private function getEventColor(EventType $eventType): string
    {
        return match ($eventType) {
            EventType::PRIVATE => '#4ade80',
            EventType::LEAGUE => '#3b82f6',
            EventType::TOURNAMENT => '#f97316',
        };
    }
}
