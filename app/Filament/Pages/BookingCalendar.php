<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Booking;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use App\Enums\EventType;
use Illuminate\Support\Collection;

final class BookingCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Calendar';
    protected static ?string $title = 'Booking Calendar';
    protected static ?int $navigationSort = 1;
    protected Collection $bookings;

    protected static string $view = 'filament.pages.booking-calendar';

    public function mount(): void
    {
        $this->bookings = Booking::query()
            ->with(['areas', 'user'])
            ->get();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    
    private function getBookingsForCalendar(): array
    {
        return $this->bookings
            ->map(function (Booking $booking) {
                $areas = $booking->areas->pluck('name')->join(', ');
                
                // Clean and format the date strings
                $date = Carbon::parse($booking->date)->format('Y-m-d');
                $startTime = Carbon::parse($booking->start_time)->format('H:i:s');
                $endTime = Carbon::parse($booking->end_time)->format('H:i:s');
                
                // Create Carbon instances
                $startDateTime = Carbon::parse($date . ' ' . $startTime);
                $endDateTime = Carbon::parse($date . ' ' . $endTime);
                
                return [
                    'id' => $booking->id,
                    'title' => $booking->user->name,
                    'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $this->getEventColor($booking->event_type),
                    'borderColor' => $this->getEventColor($booking->event_type),
                    'extendedProps' => [
                        'areas' => $areas,
                        'event_type' => $booking->event_type->value,
                        'payment_status' => $booking->payment_status->value,
                        'setup_instructions' => $booking->setup_instructions,
                    ],
                ];
            })
            ->toArray();
    }

    private function getEventColor(EventType $eventType): string
    {
        return match($eventType) {
            EventType::PRIVATE => '#4ade80',
            EventType::LEAGUE => '#3b82f6',
            EventType::TOURNAMENT => '#f97316',
        };
    }

    public function getViewData(): array
    {
        return [
            'bookings' => collect($this->getBookingsForCalendar()),
            'areas' => collect(Area::query()
                ->select(['id', 'name'])
                ->get()
                ->map(fn (Area $area) => (object)[
                    'id' => $area->id,
                    'title' => $area->name,
                ])),
        ];
    }
}