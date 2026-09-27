<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Season;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'userProducts';

    protected static ?string $recordTitleAttribute = 'user.name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),

                Select::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => Season::query()->where('is_current', true)->first()?->id),

                Select::make('status')
                    ->options(MembershipStatus::class)
                    ->required()
                    ->default(MembershipStatus::ACTIVE)
                    ->native(false),

                DateTimePicker::make('assigned_at')
                    ->label('Assigned Date')
                    ->default(now())
                    ->required(),

                DatePicker::make('expires_at')
                    ->label('Expiration Date')
                    ->helperText('Leave empty for no expiration'),

                TextInput::make('purchase_reference')
                    ->label('Purchase Reference')
                    ->helperText('Optional reference (e.g., invoice number, order ID)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('season.name')
                    ->label('Season')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MembershipStatus $state) => $state->getLabel())
                    ->color(fn (MembershipStatus $state) => $state->getColor())
                    ->sortable(),

                TextColumn::make('assigned_at')
                    ->label('Assigned')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('purchase_reference')
                    ->label('Reference')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(MembershipStatus::class)
                    ->native(false),

                SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Assign to User'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ]);
    }
}
