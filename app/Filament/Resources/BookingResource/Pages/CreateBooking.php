<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Services\RecurringBookingService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

final class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

   

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    

    protected function afterFill(): void
    {

        $this->data = [
            'areas' => array_filter(explode(',', request()->query('areas', ''))),
            'date' => request()->query('date'),
            'start_time' => request()->query('start_time'),
            'end_time' => request()->query('end_time'), 
        ];
        // Runs after the form fields are populated with their default values.
                

    }

  
    protected function handleRecordCreation(array $data): Model
    {
        // Handle recurring booking
        if ($data['is_recurring'] ?? false) {
            $recurringService = app(RecurringBookingService::class);

            // Remove recurring flag from booking data
            unset($data['is_recurring']);
            
            $bookingData = [
                'user_id' => $data['user_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'event_type' => $data['event_type'],
                'payment_status' => $data['payment_status'],
                'setup_instructions' => $data['setup_instructions'] ?? null,
                'areas' => $data['areas'],
            ];

            $patternData = [
                'frequency' => $data['recurring']['frequency'],
                'interval' => $data['recurring']['interval'],
                'start_date' => $data['date'],
                'end_date' => $data['recurring']['end_date'],
                'days_of_week' => $data['recurring']['days_of_week'] ?? null,
                'excluded_dates' => [],
            ];

            $bookings = $recurringService->createRecurringBookings($bookingData, $patternData);

            Notification::make()
                ->success()
                ->title('Recurring bookings created')
                ->body("Created {$bookings->count()} bookings successfully.")
                ->send();
            
            return $bookings->first();
        }

        // Handle non-recurring booking
        return static::getModel()::create($data);
    }
} 