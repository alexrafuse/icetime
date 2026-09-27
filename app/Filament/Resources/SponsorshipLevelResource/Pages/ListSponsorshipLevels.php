<?php

declare(strict_types=1);

namespace App\Filament\Resources\SponsorshipLevelResource\Pages;

use App\Filament\Resources\SponsorshipLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListSponsorshipLevels extends ListRecords
{
    protected static string $resource = SponsorshipLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
