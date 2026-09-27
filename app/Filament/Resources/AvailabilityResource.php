<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\AvailabilityFormBuilder;
use App\Filament\Resources\AvailabilityResource\Pages\CreateAvailability;
use App\Filament\Resources\AvailabilityResource\Pages\EditAvailability;
use App\Filament\Resources\AvailabilityResource\Pages\ListAvailabilities;
use Domain\Facility\Models\Availability;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Availabilities 🔒';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...AvailabilityFormBuilder::schema(includeArea: true),
                TextInput::make('note')
                    ->nullable()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->helperText('Optional note for holidays or special events'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('area.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('day_of_week')
                    ->formatStateUsing(fn ($state) => $state !== null ? [
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ][$state] : '')
                    ->label('Weekly Day')
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                IconColumn::make('is_available')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('note')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('day_of_week')
                    ->options([
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ])
                    ->label('Weekly Day'),
                TernaryFilter::make('is_available')
                    ->label('Availability Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilities::route('/'),
            'create' => CreateAvailability::route('/create'),
            'edit' => EditAvailability::route('/{record}/edit'),
        ];
    }
}
