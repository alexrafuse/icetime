<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserActivityResource;
use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('userActivity')
                ->label('User Activity')
                ->icon('heroicon-o-clock')
                ->url(UserActivityResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
