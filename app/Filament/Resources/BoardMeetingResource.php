<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BoardMeetingResource\Pages\CreateBoardMeeting;
use App\Filament\Resources\BoardMeetingResource\Pages\EditBoardMeeting;
use App\Filament\Resources\BoardMeetingResource\Pages\ListBoardMeetings;
use App\Filament\Resources\BoardMeetingResource\RelationManagers\AgendasRelationManager;
use App\Filament\Resources\BoardMeetingResource\RelationManagers\MinutesRelationManager;
use Domain\Board\Enums\MeetingStatus;
use Domain\Board\Models\BoardMeeting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class BoardMeetingResource extends Resource
{
    protected static ?string $model = BoardMeeting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Executive';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description'),

                DateTimePicker::make('scheduled_at')
                    ->required()
                    ->native(false),

                TextInput::make('location')
                    ->maxLength(255),

                Select::make('status')
                    ->options(collect(MeetingStatus::cases())->mapWithKeys(
                        fn (MeetingStatus $status) => [$status->value => $status->getLabel()]
                    ))
                    ->required()
                    ->default(MeetingStatus::SCHEDULED->value),

                Select::make('called_by_user_id')
                    ->label('Called By')
                    ->relationship('calledBy', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MeetingStatus $state) => $state->getLabel())
                    ->color(fn (MeetingStatus $state) => $state->getColor()),

                TextColumn::make('calledBy.name')
                    ->label('Called By')
                    ->toggleable(),

                TextColumn::make('agendas_count')
                    ->counts('agendas')
                    ->label('Agenda Items'),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(MeetingStatus::cases())->mapWithKeys(
                        fn (MeetingStatus $status) => [$status->value => $status->getLabel()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AgendasRelationManager::class,
            MinutesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoardMeetings::route('/'),
            'create' => CreateBoardMeeting::route('/create'),
            'edit' => EditBoardMeeting::route('/{record}/edit'),
        ];
    }
}
