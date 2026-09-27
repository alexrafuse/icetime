<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubBylawResource\Pages;

use App\Filament\Resources\ClubBylawResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditClubBylaw extends EditRecord
{
    protected static string $resource = ClubBylawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
