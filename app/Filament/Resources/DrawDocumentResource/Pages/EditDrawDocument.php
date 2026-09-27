<?php

declare(strict_types=1);

namespace App\Filament\Resources\DrawDocumentResource\Pages;

use App\Filament\Resources\DrawDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditDrawDocument extends EditRecord
{
    protected static string $resource = DrawDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
