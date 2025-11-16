<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Shared\Enums\RecurrencePeriod;
use App\Filament\Resources\SurveyResource\Pages;
use Domain\Shared\Models\Survey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Survey Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3)
                            ->helperText('Brief description shown to members before they click'),

                        Forms\Components\TextInput::make('tally_form_url')
                            ->label('Tally Form URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->prefix('https://')
                            ->placeholder('tally.so/r/abc123')
                            ->helperText('Full URL to your Tally form'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active surveys are shown to members'),

                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(999)
                            ->minValue(1)
                            ->helperText('Lower number = higher priority (1 shows first)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Scheduling')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->helperText('Survey will not show before this date (optional)'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('Survey will not show after this date (optional)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Recurring Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Recurring Survey')
                            ->live()
                            ->helperText('Allow users to respond multiple times based on period'),

                        Forms\Components\Select::make('recurrence_period')
                            ->label('Recurrence Period')
                            ->options(RecurrencePeriod::class)
                            ->visible(fn (Forms\Get $get) => $get('is_recurring'))
                            ->required(fn (Forms\Get $get) => $get('is_recurring'))
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('responses_count')
                    ->label('Responses')
                    ->counts('responses')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'success',
                        $state <= 10 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No start date')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No end date')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Recurring')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All surveys')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('Recurring')
                    ->placeholder('All surveys')
                    ->trueLabel('Recurring only')
                    ->falseLabel('One-time only'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }
}
