<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Enums\PaymentStatus;
use App\Filament\Forms\BookingFormBuilder;
use App\Filament\Forms\RecurringPatternFormBuilder;
use App\Filament\Resources\BookingResource\Pages;
use Domain\Booking\Models\Booking;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Manage';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Bookings 🔒';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BookingFormBuilder::groupedSchema(columns: 2),

                Section::make('Recurring Booking')
                    ->schema([
                        Forms\Components\Select::make('recurring_pattern_id')
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
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (EventType $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => $state->getColor()),
                Tables\Columns\TextColumn::make('areas.name')
                    ->badge()
                    ->separator(',')
                    ->wrap(),
                Tables\Columns\IconColumn::make('recurring_pattern_id')
                    ->label('Recurring')
                    ->boolean()
                    ->action(
                        Tables\Actions\Action::make('viewPattern')
                            ->url(fn ($record) => $record->recurring_pattern_id
                                ? RecurringPatternResource::getUrl('edit', ['record' => $record->recurring_pattern_id])
                                : null)
                            ->visible(fn ($record) => $record->recurring_pattern_id !== null)
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('event_type')
                    ->options(EventType::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('areas')
                    ->relationship('areas', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
