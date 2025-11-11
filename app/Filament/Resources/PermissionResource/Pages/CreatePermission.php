<?php

declare(strict_types=1);

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
