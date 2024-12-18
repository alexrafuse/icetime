<?php

declare(strict_types=1);

namespace App\Filament\Resources\DrawDocumentResource\Pages;

use App\Filament\Resources\DrawDocumentResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDrawDocument extends CreateRecord
{
    protected static string $resource = DrawDocumentResource::class;
} 