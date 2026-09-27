<?php

declare(strict_types=1);

namespace App\Filament\Resources\DonorResource\RelationManagers;

use Domain\Board\Enums\DonationType;
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

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    protected static ?string $recordTitleAttribute = 'donated_at';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(collect(DonationType::cases())->mapWithKeys(
                        fn (DonationType $type) => [$type->value => $type->getLabel()]
                    ))
                    ->required(),

                TextInput::make('amount_cents')
                    ->label('Amount (cents)')
                    ->numeric(),

                Textarea::make('description'),

                DatePicker::make('donated_at')
                    ->required()
                    ->native(false),

                Select::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->preload(),

                TextInput::make('receipt_number')
                    ->maxLength(100),

                Toggle::make('is_tax_receipted')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (DonationType $state) => $state->getLabel())
                    ->color(fn (DonationType $state) => $state->getColor()),

                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (?int $state) => $state ? '$'.number_format($state / 100, 2) : 'N/A')
                    ->sortable(),

                TextColumn::make('donated_at')
                    ->date()
                    ->sortable(),

                TextColumn::make('season.name')
                    ->label('Season'),

                IconColumn::make('is_tax_receipted')
                    ->boolean()
                    ->label('Receipted'),
            ])
            ->defaultSort('donated_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
