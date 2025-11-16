<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Shared\Enums\ResourceCategory;
use App\Filament\Resources\ResourceResource\Pages;
use Domain\Shared\Models\Resource as ResourceModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Resources';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Resource Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3)
                            ->helperText('Brief description shown to members'),

                        Forms\Components\Select::make('category')
                            ->options(ResourceCategory::class)
                            ->required()
                            ->native(false)
                            ->helperText('Category helps organize resources for members'),

                        Forms\Components\Select::make('type')
                            ->options([
                                'url' => 'External URL',
                                'file' => 'File Upload',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state === 'url') {
                                    $set('file_path', null);
                                } else {
                                    $set('url', null);
                                }
                            })
                            ->helperText('Choose whether to link to an external URL or upload a file'),

                        Forms\Components\TextInput::make('url')
                            ->label('External URL')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('type') === 'url')
                            ->required(fn (Get $get): bool => $get('type') === 'url')
                            ->prefix('https://')
                            ->placeholder('example.com/page')
                            ->helperText('Full URL to the external resource'),

                        Forms\Components\FileUpload::make('file_path')
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

                        Forms\Components\Select::make('visibility')
                            ->options([
                                'all' => 'All Users',
                                'admin_staff_only' => 'Admin & Staff Only',
                            ])
                            ->required()
                            ->default('all')
                            ->native(false)
                            ->helperText('Control who can view this resource'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active resources are visible to members'),

                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(999)
                            ->minValue(1)
                            ->helperText('Lower number = higher priority (shows first in category)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Validity Period')
                    ->description('Optional: Set a date range when this resource should be visible')
                    ->schema([
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Valid From')
                            ->native(false),

                        Forms\Components\DatePicker::make('valid_until')
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (ResourceCategory $state): string => $state->getLabel())
                    ->color(fn (ResourceCategory $state): string => $state->getColor()),

                Tables\Columns\TextColumn::make('type')
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

                Tables\Columns\TextColumn::make('visibility')
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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'success',
                        $state <= 10 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('category')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(ResourceCategory::class)
                    ->label('Category'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'url' => 'External URL',
                        'file' => 'File',
                    ])
                    ->label('Type'),

                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        'all' => 'All Users',
                        'admin_staff_only' => 'Admin & Staff Only',
                    ])
                    ->label('Visibility'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All resources')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon(fn (ResourceModel $record): string => $record->isUrl() ? 'heroicon-m-arrow-top-right-on-square' : 'heroicon-m-eye')
                    ->url(fn (ResourceModel $record): ?string => $record->isUrl() ? $record->url : $record->getFileUrl())
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (ResourceModel $record) {
                        if ($record->file_path) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'view' => Pages\ViewResource::route('/{record}'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }
}
