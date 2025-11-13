<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpareAvailabilityResource\Pages;

use App\Filament\Resources\SpareAvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpareAvailability extends CreateRecord
{
    protected static string $resource = SpareAvailabilityResource::class;

    protected ?string $heading = 'Set your spare availability';

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Save Preferences');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the user_id if not already set (for users without 'manage spares' permission)
        if (! auth()->user()->can('manage spares')) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}
