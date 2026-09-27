<?php

declare(strict_types=1);

namespace App\Filament\Resources\BoardMeetingResource\Pages;

use App\Filament\Resources\BoardMeetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBoardMeetings extends ListRecords
{
    protected static string $resource = BoardMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
