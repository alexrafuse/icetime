<?php

declare(strict_types=1);

namespace App\Filament\Resources\DrawDocumentResource\Pages;

use App\Filament\Resources\DrawDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListDrawDocuments extends ListRecords
{
    protected static string $resource = DrawDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableContentBeforeActions(): ?string
    {
        return view('filament.pages.draw-documents.info-banner')->render();
    }
} 