<?php

declare(strict_types=1);

namespace App\Filament\Resources\SponsorshipLevelResource\Pages;

use App\Filament\Resources\SponsorshipLevelResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSponsorshipLevel extends CreateRecord
{
    protected static string $resource = SponsorshipLevelResource::class;
}
