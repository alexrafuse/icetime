<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\EventType;
use App\Http\Resources\FullCalArea;
use App\Http\Resources\FullCalBooking;
use Domain\Booking\Models\Booking;
use Domain\Facility\Models\Area;
use Filament\Pages\Page;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;

final class BookingCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?string $title = 'Booking Calendar';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Members Area';

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

    private function getEventColor(EventType $eventType): string
    {
        return match ($eventType) {
            EventType::PRIVATE => '#4ade80',
            EventType::LEAGUE => '#3b82f6',
            EventType::TOURNAMENT => '#f97316',
            EventType::DROP_IN => '#06b6d4',
        };
    }

    protected function getViewData(): array
    {
        return [
            'bookings' => FullCalBooking::collection($this->bookings)->resolve(),
            'areas' => FullCalArea::collection(Area::all())->resolve(),
        ];
    }

    protected function getHeadComponents(): array
    {
        return [
            ...parent::getHeadComponents(),
            Vite::asset('resources/js/calendar.js'),
        ];
    }

    public function getScripts(): array
    {
        return [
            Js::make('calendar', resource_path('js/calendar.js')),
        ];
    }

    public function getStyles(): array
    {
        return [
            Css::make('fullcalendar', 'node_modules/@fullcalendar/core/main.css'),
            Css::make('fullcalendar-timegrid', 'node_modules/@fullcalendar/timegrid/main.css'),
            Css::make('fullcalendar-daygrid', 'node_modules/@fullcalendar/daygrid/main.css'),
            Css::make('fullcalendar-resource', 'node_modules/@fullcalendar/resource-timegrid/main.css'),
        ];
    }
}
