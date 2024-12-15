<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Area;
use App\Services\BookingValidationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
    //    dd('Raw form data:', $data);

        try {

            Log::info('Creating booking with data:', ['data' => $data]);

            $validationService = app(BookingValidationService::class);
            
            // Extract areas and custom prices
            $areaIds = $data['areas'] ?? [];
            Log::info('Creating booking with areas:', ['areas' => $areaIds]);
            
            // Get the Area models collection
            $areas = Area::findMany($areaIds);
            if ($areas->isEmpty()) {
                Notification::make()
                    ->danger()
                    ->title('Validation Error')
                    ->body('!Please select at least one area for the booking.')
                    ->persistent()
                    ->send();
                    
                $this->halt();
                return false;
            }

            // Validate booking times against area availability
            $date = \Carbon\Carbon::parse($data['date']);
            $startTime = \Carbon\Carbon::parse($data['start_time']);
            $endTime = \Carbon\Carbon::parse($data['end_time']);

            $validation = $validationService->validateBooking($areas, $date, $startTime, $endTime);
            // dd([
            //     'validation' => $validation,
            //     'areas' => $areas,
            //     'date' => $date,
            //     'startTime' => $startTime,
            //     'endTime' => $endTime,
            // ]);
           
            if (!$validation) {
                Notification::make()
                    ->danger()
                    ->title('Booking Conflict')
                    ->body('One or more areas are not available during the selected time or there is a booking conflict.')
                    ->send();
                    
                $this->halt();
            }

            // Create the booking
            $booking = static::getModel()::create([
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'event_type' => $data['event_type'],
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Attach areas with custom prices
            $areaData = [];
            foreach ($areaIds as $areaId) {
                $areaData[$areaId] = [
                    'custom_price' => $customPrices[$areaId] ?? null,
                ];
            }
            $booking->areas()->attach($areaData);

            Log::info('Booking created successfully', ['booking_id' => $booking->id]);
            return $booking;

        } catch (\Exception $e) {
            Log::error('Error creating booking:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('An error occurred while creating the booking. Please try again.')
                ->persistent()
                ->send();
                
            $this->halt();
            return false;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    public function mount(): void
    {
        $this->form->fill([
            'date' => request()->query('date'),
            'start_time' => request()->query('start_time'),
            'end_time' => request()->query('end_time'),
            'areas' => explode(',', request()->query('areas')),
        ]);

        parent::mount();
    }

} 