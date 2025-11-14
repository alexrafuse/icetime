<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserActivityResource\Pages;

use App\Filament\Resources\UserActivityResource;
use Filament\Resources\Pages\ListRecords;

final class ListUserActivities extends ListRecords
{
    protected static string $resource = UserActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions - this is a read-only resource
        ];
    }
}
