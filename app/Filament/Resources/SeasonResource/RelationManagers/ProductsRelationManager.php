<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeasonResource\RelationManagers;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('curlingio_id')
                    ->label('Curling.io ID')
                    ->numeric()
                    ->unique(Product::class, 'curlingio_id', ignoreRecord: true)
                    ->helperText('Optional: ID from Curling.io system'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),

                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->rows(3),

                Select::make('product_type')
                    ->label('Product Type')
                    ->options(ProductType::class)
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('membership_tier')
                    ->label('Membership Tier')
                    ->options(MembershipTier::class)
                    ->visible(fn (Get $get) => $get('product_type') === ProductType::MEMBERSHIP)
                    ->native(false),

                Select::make('capacity')
                    ->label('Membership Capacity')
                    ->options(MembershipCapacity::class)
                    ->default(MembershipCapacity::SINGLE)
                    ->required()
                    ->native(false)
                    ->helperText('Select COUPLE for memberships that cover 2 people'),

                TextInput::make('price_cents')
                    ->label('Price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->helperText('Price in dollars (will be converted to cents)')
                    ->dehydrateStateUsing(fn ($state) => (int) ($state * 100))
                    ->formatStateUsing(fn ($state) => $state / 100),

                TextInput::make('currency')
                    ->default('CAD')
                    ->required()
                    ->maxLength(3),

                Toggle::make('is_available')
                    ->label('Available for Purchase')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('product_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (ProductType $state) => $state->getLabel())
                    ->color(fn (ProductType $state) => $state->getColor())
                    ->sortable(),

                TextColumn::make('membership_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (?MembershipTier $state) => $state?->getLabel() ?? '-')
                    ->color(fn (?MembershipTier $state) => $state?->getColor() ?? 'gray')
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->badge()
                    ->formatStateUsing(fn (MembershipCapacity $state) => $state->getLabel())
                    ->color(fn (MembershipCapacity $state) => $state === MembershipCapacity::COUPLE ? 'info' : 'gray')
                    ->sortable(),

                TextColumn::make('price_cents')
                    ->label('Price')
                    ->money('CAD', divideBy: 100)
                    ->sortable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Purchases')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->options(ProductType::class)
                    ->native(false),

                SelectFilter::make('membership_tier')
                    ->label('Membership Tier')
                    ->options(MembershipTier::class)
                    ->native(false),

                SelectFilter::make('capacity')
                    ->label('Capacity')
                    ->options(MembershipCapacity::class)
                    ->native(false),

                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All products')
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('toggle_availability')
                    ->label(fn (Product $record) => $record->is_available ? 'Disable' : 'Enable')
                    ->icon(fn (Product $record) => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Product $record) => $record->is_available ? 'warning' : 'success')
                    ->action(fn (Product $record) => $record->update(['is_available' => ! $record->is_available])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_available' => true])),

                    BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_available' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
