<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SpareAvailabilityResource\Pages\CreateSpareAvailability;
use App\Filament\Resources\SpareAvailabilityResource\Pages\EditSpareAvailability;
use App\Filament\Resources\SpareAvailabilityResource\Pages\ListSpareAvailabilities;
use Domain\Facility\Models\SpareAvailability;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SpareAvailabilityResource extends Resource
{
    protected static ?string $model = SpareAvailability::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Spare List';

    protected static ?string $heading = 'Spare List';

    protected static ?string $pluralModelLabel = 'Spare List';

    protected static ?string $modelLabel = 'Spare Availability';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->visible(fn () => auth()->user()->can('manage spares')),
                Section::make('Availability')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('I am available to spare')
                            ->default(true)
                            ->inline(false)
                            ->live(),
                        Grid::make(4)
                            ->schema([
                                Toggle::make('monday')
                                    ->label('Monday Night')
                                    ->inline(false)
                                    ->visible(false),
                                Toggle::make('tuesday')
                                    ->label('Tuesday Night')
                                    ->inline(false),
                                Toggle::make('wednesday')
                                    ->label('Wednesday Night')
                                    ->inline(false),
                                Toggle::make('thursday')
                                    ->label('Thursday Night')
                                    ->inline(false),
                                Toggle::make('friday')
                                    ->label('Friday Night')
                                    ->inline(false),
                            ])
                            ->visible(fn (Get $get) => $get('is_active')),
                    ]),
                Section::make('Contact Preference')
                    ->schema([
                        TextInput::make('phone_number')
                            ->tel()
                            ->nullable(),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('sms_enabled')
                                    ->label('Available via SMS')
                                    ->inline(false),
                                Toggle::make('call_enabled')
                                    ->label('Available via Phone Call')
                                    ->inline(false),
                            ]),
                    ]),
                Section::make('Additional Information')
                    ->description('Share any relevant details that will help teams find the right spare.')
                    ->schema([
                        Textarea::make('notes')
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
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('monday')
                    ->boolean(),
                IconColumn::make('tuesday')
                    ->boolean(),
                IconColumn::make('wednesday')
                    ->boolean(),
                IconColumn::make('thursday')
                    ->boolean(),
                IconColumn::make('friday')
                    ->boolean(),
                TextColumn::make('phone_number'),
                IconColumn::make('sms_enabled')
                    ->boolean(),
                IconColumn::make('call_enabled')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('days')
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
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (SpareAvailability $record) => auth()->user()->can('update', $record)),
                DeleteAction::make()
                    ->visible(fn (SpareAvailability $record) => auth()->user()->can('delete', $record)),
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
            'index' => ListSpareAvailabilities::route('/'),
            'create' => CreateSpareAvailability::route('/create'),
            'edit' => EditSpareAvailability::route('/{record}/edit'),
        ];
    }
}
