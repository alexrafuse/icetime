<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecurringPatternResource\Pages;

use App\Filament\Resources\RecurringPatternResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRecurringPatterns extends ListRecords
{
    protected static string $resource = RecurringPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}