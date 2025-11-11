<?php

declare(strict_types=1);

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListAreas extends ListRecords
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableContentBeforeActions(): ?string
    {
        return view('filament.pages.areas.info-banner')->render();
    }
}
