<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpareAvailabilityResource\Pages;

use App\Filament\Resources\SpareAvailabilityResource;
use Domain\Facility\Models\SpareAvailability;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class ListSpareAvailabilities extends ListRecords
{
    protected static string $resource = SpareAvailabilityResource::class;

    protected string $view = 'filament.resources.spare-availability-resource.pages.list-spare-availabilities';

    #[Url]
    public ?string $findDay = null;

    public ?string $customDate = null;

    public function mount(): void
    {
        parent::mount();

        if ($this->findDay === null) {
            $today = now()->dayOfWeekIso;
            $this->findDay = match ($today) {
                2 => 'tuesday',
                3 => 'wednesday',
                4 => 'thursday',
                5 => 'friday',
                default => 'tuesday',
            };
        }
    }

    public function userHasSpareAvailability(): bool
    {
        return auth()->user()->spareAvailability !== null;
    }

    public function getCreateUrl(): string
    {
        return SpareAvailabilityResource::getUrl('create');
    }

    public function selectDay(string $day): void
    {
        $this->findDay = $day;
        $this->customDate = null;
    }

    public function selectDate(string $date): void
    {
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeekIso;

        $day = match ($dayOfWeek) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            default => null,
        };

        if ($day === null) {
            return;
        }

        $this->findDay = $day;
        $this->customDate = $carbon->format('D, M j');
    }

    public function pickDateAction(): Action
    {
        return Action::make('pickDate')
            ->label('Pick a date')
            ->icon('heroicon-o-calendar')
            ->iconButton()
            ->color('gray')
            ->size('xl')
            ->form([
                DatePicker::make('date')
                    ->label('Select a weekday')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection(),
            ])
            ->action(function (array $data) {
                $this->selectDate($data['date']);
            });
    }

    public function clearCustomDate(): void
    {
        $this->customDate = null;

        $today = now()->dayOfWeekIso;
        $this->findDay = match ($today) {
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            default => 'tuesday',
        };
    }

    public function getAvailableSpares(): Collection
    {
        if (! $this->findDay) {
            return collect();
        }

        return SpareAvailability::query()
            ->with('user')
            ->where('is_active', true)
            ->where($this->findDay, true)
            ->where('user_id', '!=', auth()->id())
            ->get();
    }

    public function getWeekDays(): array
    {
        $startOfWeek = now()->startOfWeek();

        return [
            [
                'key' => 'tuesday',
                'label' => 'Tue',
                'date' => $startOfWeek->copy()->addDay()->format('M j'),
                'full_date' => $startOfWeek->copy()->addDay()->toDateString(),
                'is_past' => $startOfWeek->copy()->addDay()->isPast() && ! $startOfWeek->copy()->addDay()->isToday(),
                'is_today' => $startOfWeek->copy()->addDay()->isToday(),
            ],
            [
                'key' => 'wednesday',
                'label' => 'Wed',
                'date' => $startOfWeek->copy()->addDays(2)->format('M j'),
                'full_date' => $startOfWeek->copy()->addDays(2)->toDateString(),
                'is_past' => $startOfWeek->copy()->addDays(2)->isPast() && ! $startOfWeek->copy()->addDays(2)->isToday(),
                'is_today' => $startOfWeek->copy()->addDays(2)->isToday(),
            ],
            [
                'key' => 'thursday',
                'label' => 'Thu',
                'date' => $startOfWeek->copy()->addDays(3)->format('M j'),
                'full_date' => $startOfWeek->copy()->addDays(3)->toDateString(),
                'is_past' => $startOfWeek->copy()->addDays(3)->isPast() && ! $startOfWeek->copy()->addDays(3)->isToday(),
                'is_today' => $startOfWeek->copy()->addDays(3)->isToday(),
            ],
            [
                'key' => 'friday',
                'label' => 'Fri',
                'date' => $startOfWeek->copy()->addDays(4)->format('M j'),
                'full_date' => $startOfWeek->copy()->addDays(4)->toDateString(),
                'is_past' => $startOfWeek->copy()->addDays(4)->isPast() && ! $startOfWeek->copy()->addDays(4)->isToday(),
                'is_today' => $startOfWeek->copy()->addDays(4)->isToday(),
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        $userSpareAvailability = auth()->user()->spareAvailability;

        return [
            Action::make('editMyPreferences')
                ->label('Edit My Preferences')
                ->icon('heroicon-o-pencil')
                ->url(fn () => SpareAvailabilityResource::getUrl('edit', ['record' => $userSpareAvailability]))
                ->visible(fn () => $userSpareAvailability !== null),
            CreateAction::make()
                ->visible(fn () => $userSpareAvailability === null),
        ];
    }
}
