<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecurringPatternResource\Pages;

use App\Filament\Resources\RecurringPatternResource;
use App\Services\RecurringBookingService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditRecurringPattern extends EditRecord
{
    protected static string $resource = RecurringPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Delete all related bookings
                    $this->record->bookings()->delete();
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Regenerate all bookings
        try {
            $service = app(RecurringBookingService::class);
            
            // Delete existing non-exception bookings
            $this->record->bookings()
                ->where('is_exception', false)
                ->delete();
            
            // Generate new bookings
            $service->regenerateBookings($this->record);
            
            Notification::make()
                ->success()
                ->title('Pattern updated')
                ->body('Bookings have been regenerated successfully.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error regenerating bookings')
                ->body($e->getMessage())
                ->send();
        }
    }
} 