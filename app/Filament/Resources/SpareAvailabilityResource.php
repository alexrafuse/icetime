<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SpareAvailabilityResource\Pages;
use Domain\Facility\Models\SpareAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SpareAvailabilityResource extends Resource
{
    protected static ?string $model = SpareAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Spare List';

    protected static ?string $heading = 'Spare List';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->visible(fn () => auth()->user()->can('manage spares')),
                Forms\Components\Section::make('Availability')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('I am available to spare')
                            ->default(true)
                            ->inline(false)
                            ->live(),
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Toggle::make('monday')
                                    ->label('Monday Night')
                                    ->inline(false)
                                    ->visible(false),
                                Forms\Components\Toggle::make('tuesday')
                                    ->label('Tuesday Night')
                                    ->inline(false),
                                Forms\Components\Toggle::make('wednesday')
                                    ->label('Wednesday Night')
                                    ->inline(false),
                                Forms\Components\Toggle::make('thursday')
                                    ->label('Thursday Night')
                                    ->inline(false),
                                Forms\Components\Toggle::make('friday')
                                    ->label('Friday Night')
                                    ->inline(false),
                            ])
                            ->visible(fn (Forms\Get $get) => $get('is_active')),
                    ]),
                Forms\Components\Section::make('Contact Preference')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->nullable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('sms_enabled')
                                    ->label('Available via SMS')
                                    ->inline(false),
                                Forms\Components\Toggle::make('call_enabled')
                                    ->label('Available via Phone Call')
                                    ->inline(false),
                            ]),
                    ]),
                Forms\Components\Section::make('Additional Information')
                    ->description('Share any relevant details that will help teams find the right spare.')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('e.g., Preferred position (Lead, Second, Third, Skip), curling experience, availability constraints, etc.')
                            ->helperText('Include your preferred position, years of experience, skill level, or any scheduling constraints.')
                            ->rows(4)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('manage spares')) {
                    $query->where(function ($q) {
                        $q->where('is_active', true)
                            ->orWhere('user_id', auth()->id());
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('monday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('tuesday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('wednesday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('thursday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('friday')
                    ->boolean(),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\IconColumn::make('sms_enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('call_enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('days')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['values'])) {
                            foreach ($data['values'] as $day) {
                                $query->where($day, true);
                            }
                        }
                    })
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (SpareAvailability $record) => auth()->user()->can('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SpareAvailability $record) => auth()->user()->can('delete', $record)),
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
            'index' => Pages\ListSpareAvailabilities::route('/'),
            'create' => Pages\CreateSpareAvailability::route('/create'),
            'edit' => Pages\EditSpareAvailability::route('/{record}/edit'),
        ];
    }
}
