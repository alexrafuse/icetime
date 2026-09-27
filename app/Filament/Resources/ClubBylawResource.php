<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ClubBylawResource\Pages\CreateClubBylaw;
use App\Filament\Resources\ClubBylawResource\Pages\EditClubBylaw;
use App\Filament\Resources\ClubBylawResource\Pages\ListClubBylaws;
use App\Filament\Resources\ClubBylawResource\Pages\ViewClubBylaw;
use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubBylaw;
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

final class ClubBylawResource extends Resource
{
    protected static ?string $model = ClubBylaw::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|\UnitEnum|null $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return 'Bylaws';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('article_number')
                    ->required()
                    ->maxLength(50),

                TextInput::make('slug')
                    ->maxLength(255),

                MarkdownEditor::make('content')
                    ->columnSpanFull(),

                FileUpload::make('file_path')
                    ->label('Bylaw Document (PDF)')
                    ->directory('bylaws')
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
                TextColumn::make('article_number')
                    ->label('Article')
                    ->sortable(),

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
            ->defaultSort('article_number')
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
                    ->visible(fn (ClubBylaw $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->action(fn (ClubBylaw $record) => app(DocumentService::class)->publishBylaw($record, auth()->user())),
                Action::make('create_revision')
                    ->label('New Version')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->visible(fn (ClubBylaw $record) => $record->isPublished())
                    ->requiresConfirmation()
                    ->action(fn (ClubBylaw $record) => app(DocumentService::class)->createBylawRevision($record)),
                DeleteAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bylaw Content')
                    ->schema([
                        TextEntry::make('article_number')
                            ->label('Article'),

                        TextEntry::make('title')
                            ->size(TextSize::Large)
                            ->weight('bold'),

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
            'index' => ListClubBylaws::route('/'),
            'create' => CreateClubBylaw::route('/create'),
            'view' => ViewClubBylaw::route('/{record}'),
            'edit' => EditClubBylaw::route('/{record}/edit'),
        ];
    }
}
