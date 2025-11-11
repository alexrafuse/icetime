<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Filament\Concerns\HasSecurityLabel;
use App\Filament\Resources\ProductResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use HasSecurityLabel;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Membership Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\Select::make('season_id')
                            ->label('Season')
                            ->relationship('season', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => Season::query()->where('is_current', true)->first()?->id),

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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Product Type & Pricing')
                    ->schema([
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->helperText('Store additional product information as key-value pairs')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
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

                Tables\Columns\TextColumn::make('season.name')
                    ->label('Season')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('curlingio_id')
                    ->label('Curling.io ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Purchases')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->searchable()
                    ->preload(),

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
            ->actions([
                Tables\Actions\ViewAction::make(),

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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Product Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        Infolists\Components\TextEntry::make('season.name')
                            ->label('Season')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Product Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('product_type')
                            ->label('Product Type')
                            ->badge()
                            ->formatStateUsing(fn (ProductType $state) => $state->getLabel())
                            ->color(fn (ProductType $state) => $state->getColor()),

                        Infolists\Components\TextEntry::make('membership_tier')
                            ->label('Membership Tier')
                            ->badge()
                            ->formatStateUsing(fn (?MembershipTier $state) => $state?->getLabel() ?? 'N/A')
                            ->color(fn (?MembershipTier $state) => $state?->getColor() ?? 'gray')
                            ->placeholder('N/A'),

                        Infolists\Components\TextEntry::make('capacity')
                            ->label('Capacity')
                            ->badge()
                            ->formatStateUsing(fn (MembershipCapacity $state) => $state->getLabel())
                            ->color(fn (MembershipCapacity $state) => $state === MembershipCapacity::COUPLE ? 'info' : 'gray'),

                        Infolists\Components\TextEntry::make('price_cents')
                            ->label('Price')
                            ->money('CAD', divideBy: 100)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('currency')
                            ->badge(),

                        Infolists\Components\IconEntry::make('is_available')
                            ->label('Available for Purchase')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Purchases')
                            ->state(fn (Product $record) => $record->users()->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-shopping-cart'),

                        Infolists\Components\TextEntry::make('revenue')
                            ->label('Total Revenue')
                            ->state(fn (Product $record) => $record->users()->count() * ($record->price_cents / 100))
                            ->money('CAD')
                            ->icon('heroicon-m-currency-dollar')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Curling.io Integration')
                    ->schema([
                        Infolists\Components\TextEntry::make('curlingio_id')
                            ->label('Curling.io ID')
                            ->placeholder('Not linked')
                            ->copyable(),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->label('')
                            ->placeholder('No metadata')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible()
                    ->visible(fn (Product $record) => ! empty($record->metadata)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductResource\RelationManagers\UserProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
