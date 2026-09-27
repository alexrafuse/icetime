<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Shared\Enums\RecurrencePeriod;
use App\Filament\Resources\SurveyResource\Pages\CreateSurvey;
use App\Filament\Resources\SurveyResource\Pages\EditSurvey;
use App\Filament\Resources\SurveyResource\Pages\ListSurveys;
use Domain\Shared\Models\Survey;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Manage Surveys';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Survey Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3)
                            ->helperText('Brief description shown to members before they click'),

                        TextInput::make('tally_form_url')
                            ->label('Tally Form URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->prefix('https://')
                            ->placeholder('tally.so/r/abc123')
                            ->helperText('Full URL to your Tally form'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active surveys are shown to members'),

                        TextInput::make('priority')
                            ->numeric()
                            ->default(999)
                            ->minValue(1)
                            ->helperText('Lower number = higher priority (1 shows first)'),
                    ])
                    ->columns(2),

                Section::make('Scheduling')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->helperText('Survey will not show before this date (optional)'),

                        DateTimePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('Survey will not show after this date (optional)'),
                    ])
                    ->columns(2),

                Section::make('Recurring Settings')
                    ->schema([
                        Toggle::make('is_recurring')
                            ->label('Recurring Survey')
                            ->live()
                            ->helperText('Allow users to respond multiple times based on period'),

                        Select::make('recurrence_period')
                            ->label('Recurrence Period')
                            ->options(RecurrencePeriod::class)
                            ->visible(fn (Get $get) => $get('is_recurring'))
                            ->required(fn (Get $get) => $get('is_recurring'))
                            ->native(false)
                            ->helperText('How often user responses reset'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('responses_count')
                    ->label('Responses')
                    ->counts('responses')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'success',
                        $state <= 10 => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('starts_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No start date')
                    ->toggleable(),

                TextColumn::make('ends_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No end date')
                    ->toggleable(),

                IconColumn::make('is_recurring')
                    ->label('Recurring')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All surveys')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                TernaryFilter::make('is_recurring')
                    ->label('Recurring')
                    ->placeholder('All surveys')
                    ->trueLabel('Recurring only')
                    ->falseLabel('One-time only'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSurveys::route('/'),
            'create' => CreateSurvey::route('/create'),
            'edit' => EditSurvey::route('/{record}/edit'),
        ];
    }
}
