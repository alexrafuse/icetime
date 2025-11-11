<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpareAvailabilityResource\Pages;

use App\Filament\Resources\SpareAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpareAvailability extends EditRecord
{
    protected static string $resource = SpareAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
