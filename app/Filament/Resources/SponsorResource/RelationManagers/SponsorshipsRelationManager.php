<?php

declare(strict_types=1);

namespace App\Filament\Resources\SponsorResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SponsorshipsRelationManager extends RelationManager
{
    protected static string $relationship = 'sponsorships';

    protected static ?string $recordTitleAttribute = 'season_id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sponsorship_level_id')
                    ->label('Level')
                    ->relationship('level', 'name')
                    ->required()
                    ->preload(),

                Select::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->required()
                    ->preload(),

                TextInput::make('amount_cents')
                    ->label('Amount (cents)')
                    ->numeric()
                    ->required(),

                DatePicker::make('start_date')
                    ->required()
                    ->native(false),

                DatePicker::make('end_date')
                    ->required()
                    ->native(false),

                Textarea::make('notes'),

                Toggle::make('is_confirmed')
                    ->label('Confirmed')
                    ->default(false),

                Toggle::make('is_paid')
                    ->label('Paid')
                    ->default(false),

                TextInput::make('reference')
                    ->label('Payment Reference')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('season.name')
                    ->label('Season')
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable(),

                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state) => '$'.number_format($state / 100, 2))
                    ->sortable(),

                IconColumn::make('is_confirmed')
                    ->label('Confirmed')
                    ->boolean(),

                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date(),
            ])
            ->defaultSort('start_date', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
