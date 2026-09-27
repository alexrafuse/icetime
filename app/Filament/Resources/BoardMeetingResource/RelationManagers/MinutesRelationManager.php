<?php

declare(strict_types=1);

namespace App\Filament\Resources\BoardMeetingResource\RelationManagers;

use Domain\Board\Enums\MinuteStatus;
use Domain\Board\Models\BoardMinute;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MinutesRelationManager extends RelationManager
{
    protected static string $relationship = 'minutes';

    protected static ?string $recordTitleAttribute = 'status';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('content')
                    ->columnSpanFull(),

                FileUpload::make('file_path')
                    ->label('Minutes PDF')
                    ->directory('board-minutes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()
                    ->openable(),

                Select::make('recorded_by_user_id')
                    ->label('Recorded By')
                    ->relationship('recordedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MinuteStatus $state) => $state->getLabel())
                    ->color(fn (MinuteStatus $state) => $state->getColor()),

                TextColumn::make('recordedBy.name')
                    ->label('Recorded By'),

                TextColumn::make('approved_at')
                    ->dateTime()
                    ->placeholder('Not approved'),

                TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BoardMinute $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (BoardMinute $record) => $record->approve(auth()->user())),
                Action::make('submit_for_approval')
                    ->label('Submit for Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (BoardMinute $record) => $record->status === MinuteStatus::DRAFT)
                    ->action(fn (BoardMinute $record) => $record->submitForApproval()),
            ]);
    }
}
