<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeasonResource\RelationManagers;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('curlingio_id')
                    ->label('Curling.io ID')
                    ->numeric()
                    ->unique(Product::class, 'curlingio_id', ignoreRecord: true)
                    ->helperText('Optional: ID from Curling.io system'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->rows(3),

                Forms\Components\Select::make('product_type')
                    ->label('Product Type')
                    ->options(ProductType::class)
                    ->required()
                    ->live()
                    ->native(false),

                Forms\Components\Select::make('membership_tier')
                    ->label('Membership Tier')
                    ->options(MembershipTier::class)
                    ->visible(fn (Forms\Get $get) => $get('product_type') === ProductType::MEMBERSHIP->value)
                    ->native(false),

                Forms\Components\Select::make('capacity')
                    ->label('Membership Capacity')
                    ->options(MembershipCapacity::class)
                    ->default(MembershipCapacity::SINGLE)
                    ->required()
                    ->native(false)
                    ->helperText('Select COUPLE for memberships that cover 2 people'),

                Forms\Components\TextInput::make('price_cents')
                    ->label('Price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->helperText('Price in dollars (will be converted to cents)')
                    ->dehydrateStateUsing(fn ($state) => (int) ($state * 100))
                    ->formatStateUsing(fn ($state) => $state / 100),

                Forms\Components\TextInput::make('currency')
                    ->default('CAD')
                    ->required()
                    ->maxLength(3),

                Forms\Components\Toggle::make('is_available')
                    ->label('Available for Purchase')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('product_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (ProductType $state) => $state->getLabel())
                    ->color(fn (ProductType $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('membership_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (?MembershipTier $state) => $state?->getLabel() ?? '-')
                    ->color(fn (?MembershipTier $state) => $state?->getColor() ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->badge()
                    ->formatStateUsing(fn (MembershipCapacity $state) => $state->getLabel())
                    ->color(fn (MembershipCapacity $state) => $state === MembershipCapacity::COUPLE ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Price')
                    ->money('CAD', divideBy: 100)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Purchases')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->options(ProductType::class)
                    ->native(false),

                Tables\Filters\SelectFilter::make('membership_tier')
                    ->label('Membership Tier')
                    ->options(MembershipTier::class)
                    ->native(false),

                Tables\Filters\SelectFilter::make('capacity')
                    ->label('Capacity')
                    ->options(MembershipCapacity::class)
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All products')
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_availability')
                    ->label(fn (Product $record) => $record->is_available ? 'Disable' : 'Enable')
                    ->icon(fn (Product $record) => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Product $record) => $record->is_available ? 'warning' : 'success')
                    ->action(fn (Product $record) => $record->update(['is_available' => ! $record->is_available])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_available' => true])),

                    Tables\Actions\BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_available' => false])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
