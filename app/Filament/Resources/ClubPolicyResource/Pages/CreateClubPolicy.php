<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubPolicyResource\Pages;

use App\Filament\Resources\ClubPolicyResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateClubPolicy extends CreateRecord
{
    protected static string $resource = ClubPolicyResource::class;
}
