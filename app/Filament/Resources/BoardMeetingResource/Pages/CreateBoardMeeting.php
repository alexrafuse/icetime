<?php

declare(strict_types=1);

namespace App\Filament\Resources\BoardMeetingResource\Pages;

use App\Filament\Resources\BoardMeetingResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBoardMeeting extends CreateRecord
{
    protected static string $resource = BoardMeetingResource::class;
}
