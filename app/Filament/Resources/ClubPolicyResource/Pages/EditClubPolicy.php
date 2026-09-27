<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubPolicyResource\Pages;

use App\Filament\Resources\ClubPolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditClubPolicy extends EditRecord
{
    protected static string $resource = ClubPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
