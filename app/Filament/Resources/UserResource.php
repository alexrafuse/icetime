<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Membership\Actions\AssignProductToUserAction;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Product;
use App\Domain\Membership\Models\Season;
use App\Enums\Permission;
use App\Filament\Resources\UserResource\Pages;
use Domain\User\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->native(false),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? bcrypt($state) : null)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_initial')
                            ->maxLength(10)
                            ->label('Middle Initial'),
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->maxLength(255)
                            ->helperText('Auto-generated from first/last name if not provided'),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->native(false)
                            ->maxDate(now()),
                        Forms\Components\TextInput::make('gender')
                            ->maxLength(50),
                        Forms\Components\Toggle::make('show_contact_info')
                            ->label('Show Contact Info in Directory')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secondary_phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secondary_email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('street_address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('unit')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('province_state')
                            ->label('Province/State')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_zip_code')
                            ->label('Postal/Zip Code')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Emergency Contact')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->label('Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Curling.io Integration')
                    ->schema([
                        Forms\Components\TextInput::make('curlingio_profile_id')
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name', 'first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('province_state')
                    ->label('Province')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('current_membership_status')
                    ->label('Membership')
                    ->badge()
                    ->formatStateUsing(fn (?MembershipStatus $state) => $state?->getLabel() ?? 'No Membership')
                    ->color(fn (?MembershipStatus $state) => $state?->getColor() ?? 'gray')
                    ->sortable()
                    ->visible(fn () => auth()->user()->can(Permission::VIEW_MEMBERSHIPS->value)),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verified')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('curlingio_profile_id')
                    ->label('Curling.io ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                Tables\Filters\SelectFilter::make('current_membership_status')
                    ->label('Membership Status')
                    ->options(MembershipStatus::class)
                    ->native(false)
                    ->visible(fn () => auth()->user()->can(Permission::VIEW_MEMBERSHIPS->value)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Account Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('email')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        Infolists\Components\IconEntry::make('email_verified_at')
                            ->boolean()
                            ->label('Email Verified'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Member Since'),
                        Infolists\Components\TextEntry::make('curlingio_profile_id')
                            ->label('Curling.io Profile ID')
                            ->placeholder('Not linked')
                            ->copyable(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Full Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('first_name'),
                        Infolists\Components\TextEntry::make('last_name'),
                        Infolists\Components\TextEntry::make('middle_initial')
                            ->label('Middle Initial')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->date()
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('gender')
                            ->placeholder('Not specified'),
                        Infolists\Components\IconEntry::make('show_contact_info')
                            ->boolean()
                            ->label('Show Contact in Directory'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('secondary_phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('secondary_email')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->placeholder('Not provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Address')
                    ->schema([
                        Infolists\Components\TextEntry::make('street_address')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('Not provided')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('unit')
                            ->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('city')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('province_state')
                            ->label('Province/State')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('postal_zip_code')
                            ->label('Postal/Zip Code')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),

                Infolists\Components\Section::make('Emergency Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('emergency_contact_name')
                            ->label('Name')
                            ->icon('heroicon-m-user')
                            ->placeholder('Not provided'),
                        Infolists\Components\TextEntry::make('emergency_contact_phone')
                            ->label('Phone')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),

                Infolists\Components\Section::make('Membership Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_membership_status')
                            ->label('Current Status')
                            ->badge()
                            ->formatStateUsing(fn (?MembershipStatus $state) => $state?->getLabel() ?? 'No Membership')
                            ->color(fn (?MembershipStatus $state) => $state?->getColor() ?? 'gray'),

                        Infolists\Components\RepeatableEntry::make('userProducts')
                            ->label('Products & Memberships')
                            ->schema([
                                Infolists\Components\TextEntry::make('season.name')
                                    ->label('Season')
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Product')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('product.product_type')
                                    ->label('Type')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                                    ->color(fn ($state) => $state?->getColor()),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                                    ->color(fn ($state) => $state?->getColor()),

                                Infolists\Components\TextEntry::make('assigned_at')
                                    ->label('Assigned')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('expires_at')
                                    ->label('Expires')
                                    ->dateTime()
                                    ->placeholder('No expiry'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (User $record) => auth()->user()->canViewMembershipStatus($record))
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('assign_product')
                            ->label('Assign Product')
                            ->icon('heroicon-o-plus-circle')
                            ->color('success')
                            ->visible(fn () => auth()->user()->can(Permission::MANAGE_MEMBERSHIPS->value))
                            ->form([
                                Forms\Components\Select::make('season_id')
                                    ->label('Season')
                                    ->options(Season::query()->pluck('name', 'id'))
                                    ->default(fn () => Season::query()->where('is_current', true)->first()?->id)
                                    ->required()
                                    ->live()
                                    ->searchable(),

                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(function (Forms\Get $get) {
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

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(MembershipStatus::class)
                                    ->default(MembershipStatus::ACTIVE)
                                    ->required()
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('expires_at')
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
                                    expiresAt: $data['expires_at'] ? \Carbon\Carbon::parse($data['expires_at']) : null,
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
