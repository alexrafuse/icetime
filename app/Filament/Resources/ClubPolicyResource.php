<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ClubPolicyResource\Pages\CreateClubPolicy;
use App\Filament\Resources\ClubPolicyResource\Pages\EditClubPolicy;
use App\Filament\Resources\ClubPolicyResource\Pages\ListClubPolicies;
use App\Filament\Resources\ClubPolicyResource\Pages\ViewClubPolicy;
use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubPolicy;
use Domain\Board\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ClubPolicyResource extends Resource
{
    protected static ?string $model = ClubPolicy::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'Policies';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->maxLength(255),

                MarkdownEditor::make('content')
                    ->columnSpanFull(),

                FileUpload::make('file_path')
                    ->label('Policy Document (PDF)')
                    ->directory('policies')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()
                    ->openable(),

                TextInput::make('version')
                    ->numeric()
                    ->default(1)
                    ->disabled(),

                Select::make('status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(
                        fn (DocumentStatus $status) => [$status->value => $status->getLabel()]
                    ))
                    ->default(DocumentStatus::DRAFT->value)
                    ->disabled(),

                DatePicker::make('effective_date')
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('version')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (DocumentStatus $state) => $state->getLabel())
                    ->color(fn (DocumentStatus $state) => $state->getColor()),

                TextColumn::make('effective_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not published'),
            ])
            ->defaultSort('title')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(
                        fn (DocumentStatus $status) => [$status->value => $status->getLabel()]
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->visible(fn (ClubPolicy $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->action(fn (ClubPolicy $record) => app(DocumentService::class)->publishPolicy($record, auth()->user())),
                Action::make('create_revision')
                    ->label('New Version')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->visible(fn (ClubPolicy $record) => $record->isPublished())
                    ->requiresConfirmation()
                    ->action(fn (ClubPolicy $record) => app(DocumentService::class)->createPolicyRevision($record)),
                DeleteAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Policy Content')
                    ->schema([
                        TextEntry::make('title')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (DocumentStatus $state) => $state->getLabel())
                            ->color(fn (DocumentStatus $state) => $state->getColor()),

                        TextEntry::make('version'),

                        TextEntry::make('effective_date')
                            ->date(),

                        TextEntry::make('published_at')
                            ->dateTime()
                            ->placeholder('Not published'),

                        TextEntry::make('content')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubPolicies::route('/'),
            'create' => CreateClubPolicy::route('/create'),
            'view' => ViewClubPolicy::route('/{record}'),
            'edit' => EditClubPolicy::route('/{record}/edit'),
        ];
    }
}
