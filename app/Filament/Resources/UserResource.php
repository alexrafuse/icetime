<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Actions\AssignProductToUserAction;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Enums\Permission;
use App\Enums\RoleEnum;
use App\Filament\Resources\UserResource\RelationManagers\SponsorsRelationManager;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use Carbon\Carbon;
use Domain\User\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Registration';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        DateTimePicker::make('email_verified_at')
                            ->native(false),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? bcrypt($state) : null)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Personal Information')
                    ->schema([
                        TextInput::make('first_name')
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->maxLength(255),
                        TextInput::make('middle_initial')
                            ->maxLength(10)
                            ->label('Middle Initial'),
                        TextInput::make('name')
                            ->label('Full Name')
                            ->maxLength(255)
                            ->helperText('Auto-generated from first/last name if not provided'),
                        DatePicker::make('date_of_birth')
                            ->native(false)
                            ->maxDate(now()),
                        TextInput::make('gender')
                            ->maxLength(50),
                        Toggle::make('show_contact_info')
                            ->label('Show Contact Info in Directory')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('secondary_phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('secondary_email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('street_address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('unit')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('province_state')
                            ->label('Province/State')
                            ->maxLength(255),
                        TextInput::make('postal_zip_code')
                            ->label('Postal/Zip Code')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Emergency Contact')
                    ->schema([
                        TextInput::make('emergency_contact_name')
                            ->label('Name')
                            ->maxLength(255),
                        TextInput::make('emergency_contact_phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Roles & Permissions')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value)),

                Section::make('Curling.io Integration')
                    ->schema([
                        TextInput::make('curlingio_profile_id')
                            ->label('Curling.io Profile ID')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->helperText('Automatically populated from curling.io import'),
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
                    ->searchable(['name', 'first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('province_state')
                    ->label('Province')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('current_membership_status')
                    ->label('Membership')
                    ->badge()
                    ->formatStateUsing(fn (?MembershipStatus $state) => $state?->getLabel() ?? 'No Membership')
                    ->color(fn (?MembershipStatus $state) => $state?->getColor() ?? 'gray')
                    ->sortable()
                    ->visible(fn () => auth()->user()->can(Permission::VIEW_MEMBERSHIPS->value)),

                IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('curlingio_profile_id')
                    ->label('Curling.io ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('warning')
                    ->separator(', ')
                    ->toggleable()
                    ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value)),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                SelectFilter::make('current_membership_status')
                    ->label('Membership Status')
                    ->options(MembershipStatus::class)
                    ->native(false)
                    ->visible(fn () => Auth::user()->can(Permission::VIEW_MEMBERSHIPS->value)),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
                Impersonate::make()
                    ->redirectTo(route('filament.admin.pages.dashboard'))
                    ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->schema([
                        TextEntry::make('email')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        IconEntry::make('email_verified_at')
                            ->boolean()
                            ->label('Email Verified'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Member Since'),
                        TextEntry::make('curlingio_profile_id')
                            ->label('Curling.io Profile ID')
                            ->placeholder('Not linked')
                            ->copyable(),
                        TextEntry::make('roles.name')
                            ->label('Roles')
                            ->badge()
                            ->color('warning')
                            ->placeholder('No roles assigned')
                            ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value)),
                    ])
                    ->columns(2),

                Section::make('Personal Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full Name')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('middle_initial')
                            ->label('Middle Initial')
                            ->placeholder('N/A'),
                        TextEntry::make('date_of_birth')
                            ->date()
                            ->placeholder('Not provided'),
                        TextEntry::make('gender')
                            ->placeholder('Not specified'),
                        IconEntry::make('show_contact_info')
                            ->boolean()
                            ->label('Show Contact in Directory'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                        TextEntry::make('secondary_phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                        TextEntry::make('secondary_email')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->placeholder('Not provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextEntry::make('street_address')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('Not provided')
                            ->columnSpanFull(),
                        TextEntry::make('unit')
                            ->placeholder('N/A'),
                        TextEntry::make('city')
                            ->placeholder('Not provided'),
                        TextEntry::make('province_state')
                            ->label('Province/State')
                            ->placeholder('Not provided'),
                        TextEntry::make('postal_zip_code')
                            ->label('Postal/Zip Code')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),

                Section::make('Emergency Contact')
                    ->schema([
                        TextEntry::make('emergency_contact_name')
                            ->label('Name')
                            ->icon('heroicon-m-user')
                            ->placeholder('Not provided'),
                        TextEntry::make('emergency_contact_phone')
                            ->label('Phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),

                Section::make('Membership Information')
                    ->schema([
                        TextEntry::make('current_membership_status')
                            ->label('Current Status')
                            ->badge()
                            ->formatStateUsing(fn (?MembershipStatus $state) => $state?->getLabel() ?? 'No Membership')
                            ->color(fn (?MembershipStatus $state) => $state?->getColor() ?? 'gray'),

                        RepeatableEntry::make('userProducts')
                            ->label('Products & Memberships')
                            ->schema([
                                TextEntry::make('season.name')
                                    ->label('Season')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('product.name')
                                    ->label('Product')
                                    ->weight('bold'),

                                TextEntry::make('product.product_type')
                                    ->label('Type')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                                    ->color(fn ($state) => $state?->getColor()),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                                    ->color(fn ($state) => $state?->getColor()),

                                TextEntry::make('assigned_at')
                                    ->label('Assigned')
                                    ->dateTime(),

                                TextEntry::make('expires_at')
                                    ->label('Expires')
                                    ->dateTime()
                                    ->placeholder('No expiry'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (User $record) => auth()->user()->canViewMembershipStatus($record))
                    ->headerActions([
                        Action::make('assign_product')
                            ->label('Assign Product')
                            ->icon('heroicon-o-plus-circle')
                            ->color('success')
                            ->visible(fn () => auth()->user()->can(Permission::MANAGE_MEMBERSHIPS->value))
                            ->schema([
                                Select::make('season_id')
                                    ->label('Season')
                                    ->options(Season::query()->pluck('name', 'id'))
                                    ->default(fn () => Season::query()->where('is_current', true)->first()?->id)
                                    ->required()
                                    ->live()
                                    ->searchable(),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(function (Get $get) {
                                        $seasonId = $get('season_id');
                                        if (! $seasonId) {
                                            return [];
                                        }

                                        return Product::query()
                                            ->where('season_id', $seasonId)
                                            ->where('is_available', true)
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable(),

                                Select::make('status')
                                    ->label('Status')
                                    ->options(MembershipStatus::class)
                                    ->default(MembershipStatus::ACTIVE)
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('expires_at')
                                    ->label('Expiry Date (Optional)')
                                    ->native(false),
                            ])
                            ->action(function (array $data, User $record) {
                                $action = app(AssignProductToUserAction::class);
                                $product = Product::find($data['product_id']);
                                $season = Season::find($data['season_id']);

                                $action->execute(
                                    user: $record,
                                    product: $product,
                                    season: $season,
                                    expiresAt: $data['expires_at'] ? Carbon::parse($data['expires_at']) : null,
                                    status: MembershipStatus::from($data['status'])
                                );

                                Notification::make()
                                    ->title('Product assigned successfully')
                                    ->success()
                                    ->send();
                            }),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SponsorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
