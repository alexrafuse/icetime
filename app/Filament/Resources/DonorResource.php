<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DonorResource\Pages\CreateDonor;
use App\Filament\Resources\DonorResource\Pages\EditDonor;
use App\Filament\Resources\DonorResource\Pages\ListDonors;
use App\Filament\Resources\DonorResource\RelationManagers\DonationsRelationManager;
use Domain\Board\Models\Donor;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class DonorResource extends Resource
{
    protected static ?string $model = Donor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|\UnitEnum|null $navigationGroup = 'Executive';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),

                Textarea::make('address'),

                Toggle::make('is_anonymous')
                    ->default(false),

                Textarea::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_anonymous')
                    ->boolean()
                    ->label('Anonymous'),

                TextColumn::make('donations_count')
                    ->counts('donations')
                    ->label('Donations'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_anonymous')
                    ->label('Anonymous'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DonationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonors::route('/'),
            'create' => CreateDonor::route('/create'),
            'edit' => EditDonor::route('/{record}/edit'),
        ];
    }
}
