<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubBylawResource\Pages;

use App\Filament\Resources\ClubBylawResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateClubBylaw extends CreateRecord
{
    protected static string $resource = ClubBylawResource::class;
}
