<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecurringPatternResource\Pages;

use App\Filament\Resources\RecurringPatternResource;
use App\Services\RecurringBookingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecurringPattern extends CreateRecord
{
    protected static string $resource = RecurringPatternResource::class;

    protected function afterCreate(): void
    {
        // Generate the bookings
        try {
            app(RecurringBookingService::class)->regenerateBookings($this->record);

            Notification::make()
                ->success()
                ->title('Recurring pattern created')
                ->body('Bookings have been generated successfully.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error generating bookings')
                ->body($e->getMessage())
                ->send();
        }
    }
}
