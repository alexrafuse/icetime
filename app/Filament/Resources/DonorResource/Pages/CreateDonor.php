<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonorResource\Pages;

use App\Filament\Resources\DonorResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDonor extends CreateRecord
{
    protected static string $resource = DonorResource::class;
}
