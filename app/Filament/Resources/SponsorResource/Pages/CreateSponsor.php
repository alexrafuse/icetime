<?php

declare(strict_types=1);

namespace App\Filament\Resources\SponsorResource\Pages;

use App\Filament\Resources\SponsorResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSponsor extends CreateRecord
{
    protected static string $resource = SponsorResource::class;
}
