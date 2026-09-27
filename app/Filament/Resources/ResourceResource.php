<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Shared\Enums\ResourceCategory;
use App\Filament\Resources\ResourceResource\Pages\CreateResource;
use App\Filament\Resources\ResourceResource\Pages\EditResource;
use App\Filament\Resources\ResourceResource\Pages\ListResources;
use App\Filament\Resources\ResourceResource\Pages\ViewResource;
use Domain\Shared\Models\Resource as ResourceModel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Manage Resources';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resource Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3)
                            ->helperText('Brief description shown to members'),

                        Select::make('category')
                            ->options(ResourceCategory::class)
                            ->required()
                            ->native(false)
                            ->helperText('Category helps organize resources for members'),

                        Select::make('type')
                            ->options([
                                'url' => 'External URL',
                                'file' => 'File Upload',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'url') {
                                    $set('file_path', null);
                                } else {
                                    $set('url', null);
                                }
                            })
                            ->helperText('Choose whether to link to an external URL or upload a file'),

                        TextInput::make('url')
                            ->label('External URL')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('type') === 'url')
                            ->required(fn (Get $get): bool => $get('type') === 'url')
                            ->prefix('https://')
                            ->placeholder('example.com/page')
                            ->helperText('Full URL to the external resource'),

                        FileUpload::make('file_path')
                            ->label('File')
                            ->directory('resources')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->visible(fn (Get $get): bool => $get('type') === 'file')
                            ->required(fn (Get $get): bool => $get('type') === 'file')
                            ->downloadable()
                            ->openable()
                            ->helperText('Supported: PDFs, images, Word, Excel, PowerPoint'),

                        Select::make('visibility')
                            ->options([
                                'all' => 'All Users',
                                'admin_staff_only' => 'Admin & Staff Only',
                            ])
                            ->required()
                            ->default('all')
                            ->native(false)
                            ->helperText('Control who can view this resource'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active resources are visible to members'),

                        TextInput::make('priority')
                            ->numeric()
                            ->default(999)
                            ->minValue(1)
                            ->helperText('Lower number = higher priority (shows first in category)'),
                    ])
                    ->columns(2),

                Section::make('Validity Period')
                    ->description('Optional: Set a date range when this resource should be visible')
                    ->schema([
                        DatePicker::make('valid_from')
                            ->label('Valid From')
                            ->native(false),

                        DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->after('valid_from')
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
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

                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (ResourceCategory $state): string => $state->getLabel())
                    ->color(fn (ResourceCategory $state): string => $state->getColor()),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'url' => 'External URL',
                        'file' => 'File',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'url' => 'info',
                        'file' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'url' => 'heroicon-o-link',
                        'file' => 'heroicon-o-document',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                TextColumn::make('visibility')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'All Users',
                        'admin_staff_only' => 'Admin & Staff',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'admin_staff_only' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'success',
                        $state <= 10 => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('category')
            ->filters([
                SelectFilter::make('category')
                    ->options(ResourceCategory::class)
                    ->label('Category'),

                SelectFilter::make('type')
                    ->options([
                        'url' => 'External URL',
                        'file' => 'File',
                    ])
                    ->label('Type'),

                SelectFilter::make('visibility')
                    ->options([
                        'all' => 'All Users',
                        'admin_staff_only' => 'Admin & Staff Only',
                    ])
                    ->label('Visibility'),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All resources')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon(fn (ResourceModel $record): string => $record->isUrl() ? 'heroicon-m-arrow-top-right-on-square' : 'heroicon-m-eye')
                    ->url(fn (ResourceModel $record): ?string => $record->isUrl() ? $record->url : $record->getFileUrl())
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (ResourceModel $record) {
                        if ($record->file_path) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->file_path) {
                                    Storage::disk('public')->delete($record->file_path);
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
            'create' => CreateResource::route('/create'),
            'view' => ViewResource::route('/{record}'),
            'edit' => EditResource::route('/{record}/edit'),
        ];
    }
}
