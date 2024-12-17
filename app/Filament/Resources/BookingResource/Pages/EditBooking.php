<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    // If this is part of a recurring pattern, ask if they want to delete all
                    if ($this->record->recurring_pattern_id) {
                        // Delete just this booking, keeping the pattern
                        $this->record->areas()->detach();
                        $this->record->delete();

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