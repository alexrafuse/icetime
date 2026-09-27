<?php

declare(strict_types=1);

namespace App\Filament\Resources\BoardMeetingResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgendasRelationManager extends RelationManager
{
    protected static string $relationship = 'agendas';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description'),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                TextInput::make('duration_minutes')
                    ->numeric()
                    ->suffix('minutes'),

                Select::make('presenter_user_id')
                    ->label('Presenter')
                    ->relationship('presenter', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('duration_minutes')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('presenter.name')
                    ->label('Presenter'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
