<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubBylawResource\Pages;

use App\Filament\Resources\ClubBylawResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListClubBylaws extends ListRecords
{
    protected static string $resource = ClubBylawResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('api_instructions')
                ->label('API Instructions')
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->modalHeading('API Instructions')
                ->modalDescription('Use these endpoints to fetch bylaws from your frontend application.')
                ->modalContent(fn () => view('filament.modals.api-instructions', [
                    'endpoints' => [
                        [
                            'method' => 'GET',
                            'path' => '/api/v1/bylaws',
                            'description' => 'List all published bylaws',
                        ],
                        [
                            'method' => 'GET',
                            'path' => '/api/v1/bylaws/{slug}',
                            'description' => 'Get a specific bylaw by slug',
                        ],
                    ],
                    'exampleSlug' => 'example-bylaw',
                    'type' => 'bylaws',
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
        ];
    }
}
