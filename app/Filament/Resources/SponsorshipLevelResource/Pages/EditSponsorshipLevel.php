<?php

declare(strict_types=1);

namespace App\Filament\Resources\SponsorshipLevelResource\Pages;

use App\Filament\Resources\SponsorshipLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditSponsorshipLevel extends EditRecord
{
    protected static string $resource = SponsorshipLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
