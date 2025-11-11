<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

final class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if ($this->record->recurring_pattern_id) {
                        Notification::make()
                            ->success()
                            ->title('Booking deleted')
                            ->body('The booking was deleted. Other recurring bookings were kept.')
                            ->send();

                        return redirect()->route('filament.admin.resources.bookings.index');
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->recurring_pattern_id) {
            Notification::make()
                ->warning()
                ->title('Recurring booking updated')
                ->body('Note: This update only affects this specific booking. Other recurring bookings were not changed.')
                ->send();
        }
    }
}
