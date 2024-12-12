<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Services\BookingValidationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $validationService = app(BookingValidationService::class);
        
        // Validate booking times against area availability
        $areas = \App\Models\Area::findMany($data['areas']);
        $date = \Carbon\Carbon::parse($data['date']);
        $startTime = \Carbon\Carbon::parse($data['start_time']);
        $endTime = \Carbon\Carbon::parse($data['end_time']);

        if (!$validationService->validateBooking($areas, $date, $startTime, $endTime, $record->id)) {
            $this->halt('One or more areas are not available during the selected time.');
        }

        $record->update($data);

        return $record;
    }
} 