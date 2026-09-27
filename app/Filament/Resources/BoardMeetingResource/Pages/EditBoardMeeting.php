<?php

declare(strict_types=1);

namespace App\Filament\Resources\BoardMeetingResource\Pages;

use App\Filament\Resources\BoardMeetingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditBoardMeeting extends EditRecord
{
    protected static string $resource = BoardMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
