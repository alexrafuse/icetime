<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecurringPatternResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Services\RecurringBookingService;
use App\Filament\Resources\RecurringPatternResource;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->primaryBooking) {
            $data['primaryBooking']['id'] = $this->record->primary_booking_id;
            $data['primaryBooking']['date'] = $this->record->primaryBooking->date;
   
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        
        if (isset($data['primaryBooking']['id'])) {
            $data['primary_booking_id'] = $data['primaryBooking']['id'];
        }
        
        // dont save the nested primaryBooking data
        unset($data['primaryBooking']);
        
        
        return $data;
    }

    protected function afterSave(): void
    {
        try {
            $service = app(RecurringBookingService::class);
            
            // Log the record state before operations
            Log::debug('Record state before regeneration:', [
                'pattern_id' => $this->record->id,
                'primary_booking_id' => $this->record->primary_booking_id,
                'date' => $this->record->date,
            ]);
            
            // Delete existing non-exception bookings
            $this->record->bookings()
                ->where('id', '!=', $this->record->primary_booking_id)
                ->delete();
            
            // Refresh and log the record state
            $this->record->refresh();
            Log::debug('Record state after refresh:', [
                'pattern_id' => $this->record->id,
                'primary_booking_id' => $this->record->primary_booking_id,
                'date' => $this->record->date,
            ]);
            
            // Generate new bookings
            $service->regenerateBookings($this->record);
            
            Notification::make()
                ->success()
                ->title('Pattern updated')
                ->body('Bookings have been regenerated successfully.')
                ->send();
        } catch (\Exception $e) {
            Log::error('Error regenerating bookings: ' . $e->getMessage(), [
                'pattern_id' => $this->record->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->danger()
                ->title('Error regenerating bookings')
                ->body('Failed to regenerate bookings. Please try again or contact support.')
                ->send();
        }
    }
} 