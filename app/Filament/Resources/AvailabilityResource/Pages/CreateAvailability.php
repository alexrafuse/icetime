<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvailabilityResource\Pages;

use App\Filament\Resources\AvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAvailability extends CreateRecord
{
    protected static string $resource = AvailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure we don't save both day_of_week and date
        if (! empty($data['date'])) {
            $data['day_of_week'] = null;
        }

        return $data;
    }
}
