<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpareAvailabilityResource\Pages;

use App\Filament\Resources\SpareAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpareAvailabilities extends ListRecords
{
    protected static string $resource = SpareAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 