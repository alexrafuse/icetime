<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClubPolicyResource\Pages;

use App\Filament\Resources\ClubPolicyResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListClubPolicies extends ListRecords
{
    protected static string $resource = ClubPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('api_instructions')
                ->label('API Instructions')
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->modalHeading('API Instructions')
                ->modalDescription('Use these endpoints to fetch policies from your frontend application.')
                ->modalContent(fn () => view('filament.modals.api-instructions', [
                    'endpoints' => [
                        [
                            'method' => 'GET',
                            'path' => '/api/v1/policies',
                            'description' => 'List all published policies',
                        ],
                        [
                            'method' => 'GET',
                            'path' => '/api/v1/policies/{slug}',
                            'description' => 'Get a specific policy by slug',
                        ],
                    ],
                    'exampleSlug' => 'example-policy',
                    'type' => 'policies',
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
        ];
    }
}
