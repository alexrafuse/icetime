<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Enums\MembershipCapacity;
use App\Domain\Membership\Enums\MembershipTier;
use App\Domain\Membership\Enums\ProductType;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Filament\Concerns\HasSecurityLabel;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Filament\Resources\ProductResource\RelationManagers\UserProductsRelationManager;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use HasSecurityLabel;

    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->schema([
                        Select::make('season_id')
                            ->label('Season')
                            ->relationship('season', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => Season::query()->where('is_current', true)->first()?->id),

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
                    ])
                    ->columns(2),

                Section::make('Product Type & Pricing')
                    ->schema([
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
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        KeyValue::make('metadata')
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

                TextColumn::make('season.name')
                    ->label('Season')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('curlingio_id')
                    ->label('Curling.io ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Purchases')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'name')
                    ->searchable()
                    ->preload(),

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
            ->recordActions([
                ViewAction::make(),

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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->schema([
                        TextEntry::make('name')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        TextEntry::make('season.name')
                            ->label('Season')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('description')
                            ->placeholder('No description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Product Details')
                    ->schema([
                        TextEntry::make('product_type')
                            ->label('Product Type')
                            ->badge()
                            ->formatStateUsing(fn (ProductType $state) => $state->getLabel())
                            ->color(fn (ProductType $state) => $state->getColor()),

                        TextEntry::make('membership_tier')
                            ->label('Membership Tier')
                            ->badge()
                            ->formatStateUsing(fn (?MembershipTier $state) => $state?->getLabel() ?? 'N/A')
                            ->color(fn (?MembershipTier $state) => $state?->getColor() ?? 'gray')
                            ->placeholder('N/A'),

                        TextEntry::make('capacity')
                            ->label('Capacity')
                            ->badge()
                            ->formatStateUsing(fn (MembershipCapacity $state) => $state->getLabel())
                            ->color(fn (MembershipCapacity $state) => $state === MembershipCapacity::COUPLE ? 'info' : 'gray'),

                        TextEntry::make('price_cents')
                            ->label('Price')
                            ->money('CAD', divideBy: 100)
                            ->size(TextSize::Large)
                            ->weight('bold'),

                        TextEntry::make('currency')
                            ->badge(),

                        IconEntry::make('is_available')
                            ->label('Available for Purchase')
                            ->boolean(),
                    ])
                    ->columns(3),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('users_count')
                            ->label('Total Purchases')
                            ->state(fn (Product $record) => $record->users()->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-shopping-cart'),

                        TextEntry::make('revenue')
                            ->label('Total Revenue')
                            ->state(fn (Product $record) => $record->users()->count() * ($record->price_cents / 100))
                            ->money('CAD')
                            ->icon('heroicon-m-currency-dollar')
                            ->color('success'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2),

                Section::make('Curling.io Integration')
                    ->schema([
                        TextEntry::make('curlingio_id')
                            ->label('Curling.io ID')
                            ->placeholder('Not linked')
                            ->copyable(),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Section::make('Metadata')
                    ->schema([
                        KeyValueEntry::make('metadata')
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
            UserProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
