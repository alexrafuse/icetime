<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\EventType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\FrequencyType;
use App\Models\RecurringPattern;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RecurringPatternResource\Pages\EditRecurringPattern;
use App\Filament\Resources\RecurringPatternResource\Pages\ListRecurringPatterns;
use App\Filament\Resources\RecurringPatternResource\Pages\CreateRecurringPattern;

final class RecurringPatternResource extends Resource
{
    protected static ?string $model = RecurringPattern::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Bookings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('frequency')
                ->options(FrequencyType::class)
                ->required(),

            Forms\Components\TextInput::make('interval')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),

            Forms\Components\DatePicker::make('start_date')
                ->required()
                ->native(false),

            Forms\Components\DatePicker::make('end_date')
                ->native(false)
                ->after('start_date'),

            Forms\Components\CheckboxList::make('days_of_week')
                ->options([
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    7 => 'Sunday',
                ])
                ->columns(4)
                ->required(),

            Forms\Components\TimePicker::make('start_time')
                ->required()
                ->native(false),

            Forms\Components\TimePicker::make('end_time')
                ->required()
                ->native(false)
                ->after('start_time'),

            Forms\Components\Select::make('event_type')
                ->options(EventType::class)
                ->required(),

            Forms\Components\Textarea::make('setup_instructions')
                ->maxLength(65535)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('interval')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('days_of_week')
                    ->formatStateUsing(fn (array $state): string => collect($state)
                        ->map(fn (int $day) => match($day) {
                            1 => 'Mon',
                            2 => 'Tue',
                            3 => 'Wed',
                            4 => 'Thu',
                            5 => 'Fri',
                            6 => 'Sat',
                            7 => 'Sun',
                        })
                        ->join(', ')
                    ),

                Tables\Columns\TextColumn::make('start_time')
                    ->time()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->time()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options(EventType::class),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('end_date', '>=', now())
                        ->orWhereNull('end_date')
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (RecurringPattern $record) {
                        // Delete all related bookings
                        $record->bookings()->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            // Delete all related bookings
                            $records->each(fn ($record) => $record->bookings()->delete());
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecurringPatterns::route('/'),
            'create' => CreateRecurringPattern::route('/create'),
            'edit' => EditRecurringPattern::route('/{record}/edit'),
        ];
    }
} 