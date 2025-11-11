<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Season;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UserProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'userProducts';

    protected static ?string $recordTitleAttribute = 'user.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),

                Forms\Components\Select::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => Season::query()->where('is_current', true)->first()?->id),

                Forms\Components\Select::make('status')
                    ->options(MembershipStatus::class)
                    ->required()
                    ->default(MembershipStatus::ACTIVE)
                    ->native(false),

                Forms\Components\DateTimePicker::make('assigned_at')
                    ->label('Assigned Date')
                    ->default(now())
                    ->required(),

                Forms\Components\DatePicker::make('expires_at')
                    ->label('Expiration Date')
                    ->helperText('Leave empty for no expiration'),

                Forms\Components\TextInput::make('purchase_reference')
                    ->label('Purchase Reference')
                    ->helperText('Optional reference (e.g., invoice number, order ID)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('season.name')
                    ->label('Season')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MembershipStatus $state) => $state->getLabel())
                    ->color(fn (MembershipStatus $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('assigned_at')
                    ->label('Assigned')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('purchase_reference')
                    ->label('Reference')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('assigned_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(MembershipStatus::class)
                    ->native(false),

                Tables\Filters\SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Assign to User'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ]);
    }
}
