<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubBylawResource\Pages;

use App\Filament\Resources\ClubBylawResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewClubBylaw extends ViewRecord
{
    protected static string $resource = ClubBylawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
