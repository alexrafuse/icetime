<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubPolicyResource\Pages;

use App\Filament\Resources\ClubPolicyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewClubPolicy extends ViewRecord
{
    protected static string $resource = ClubPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
