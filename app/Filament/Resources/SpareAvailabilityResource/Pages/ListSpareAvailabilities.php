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
        $userSpareAvailability = auth()->user()->spareAvailability;

        return [
            Actions\Action::make('editMyPreferences')
                ->label('Edit My Preferences')
                ->icon('heroicon-o-pencil')
                ->url(fn () => SpareAvailabilityResource::getUrl('edit', ['record' => $userSpareAvailability]))
                ->visible(fn () => $userSpareAvailability !== null),
            Actions\CreateAction::make()
                ->visible(fn () => $userSpareAvailability === null),
        ];
    }
}
