<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Booking;
use App\Enums\EventType;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Vite;
use App\Http\Resources\FullCalBooking;
use App\Http\Resources\FullCalArea;

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
    
  

    private function getEventColor(EventType $eventType): string
    {
        return match($eventType) {
            EventType::PRIVATE => '#4ade80',
            EventType::LEAGUE => '#3b82f6',
            EventType::TOURNAMENT => '#f97316',
        };
    }

    protected function getViewData(): array
    {
        return [
            'bookings' => FullCalBooking::collection($this->bookings)->resolve(),
            'areas' =>FullCalArea::collection(Area::all())->resolve(),
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