<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\RecurringPatternResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('recurringPatterns')
                ->label('Recurring Patterns')
                ->icon('heroicon-o-arrow-path')
                ->url(RecurringPatternResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getTableContentBeforeActions(): ?string
    {
        return view('filament.pages.bookings.info-banner')->render();
    }
}
