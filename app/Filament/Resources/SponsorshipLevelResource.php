<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorshipLevelResource\Pages\CreateSponsorshipLevel;
use App\Filament\Resources\SponsorshipLevelResource\Pages\EditSponsorshipLevel;
use App\Filament\Resources\SponsorshipLevelResource\Pages\ListSponsorshipLevels;
use Domain\Board\Models\SponsorshipLevel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class SponsorshipLevelResource extends Resource
{
    protected static ?string $model = SponsorshipLevel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description'),

                TextInput::make('amount_cents')
                    ->label('Minimum Amount (cents)')
                    ->numeric()
                    ->required(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state) => '$'.number_format($state / 100, 2))
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('sponsorships_count')
                    ->counts('sponsorships')
                    ->label('Sponsorships'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSponsorshipLevels::route('/'),
            'create' => CreateSponsorshipLevel::route('/create'),
            'edit' => EditSponsorshipLevel::route('/{record}/edit'),
        ];
    }
}
