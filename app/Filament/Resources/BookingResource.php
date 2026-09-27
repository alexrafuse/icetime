<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Filament\Forms\BookingFormBuilder;
use App\Filament\Forms\RecurringPatternFormBuilder;
use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use Domain\Booking\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Bookings 🔒';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                BookingFormBuilder::groupedSchema(columns: 2),

                Section::make('Recurring Booking')
                    ->schema([
                        Select::make('recurring_pattern_id')
                            ->relationship(
                                name: 'recurringPattern',
                                titleAttribute: 'title'
                            )
                            ->createOptionForm(RecurringPatternFormBuilder::createSchema())
                            ->editOptionForm(RecurringPatternFormBuilder::editSchema()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (EventType $state): string => $state->getColor()),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => $state->getColor()),
                TextColumn::make('areas.name')
                    ->badge()
                    ->separator(',')
                    ->wrap(),
                IconColumn::make('recurring_pattern_id')
                    ->label('Recurring')
                    ->boolean()
                    ->action(
                        Action::make('viewPattern')
                            ->url(fn ($record) => $record->recurring_pattern_id
                                ? RecurringPatternResource::getUrl('edit', ['record' => $record->recurring_pattern_id])
                                : null)
                            ->visible(fn ($record) => $record->recurring_pattern_id !== null)
                    ),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event_type')
                    ->options(EventType::class),
                SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('areas')
                    ->relationship('areas', 'name')
                    ->multiple()
                    ->preload(),
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
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
