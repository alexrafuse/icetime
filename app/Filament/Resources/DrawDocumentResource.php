<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DrawDocumentResource\Pages\CreateDrawDocument;
use App\Filament\Resources\DrawDocumentResource\Pages\EditDrawDocument;
use App\Filament\Resources\DrawDocumentResource\Pages\ListDrawDocuments;
use Domain\Shared\Models\DrawDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class DrawDocumentResource extends Resource
{
    protected static ?string $model = DrawDocument::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Draw Schedules';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('day_of_week')
                    ->options(DrawDocument::getDayNames())
                    ->required(),

                FileUpload::make('file_path')
                    ->label('PDF File')
                    ->directory('draws')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->downloadable()
                    ->openable(),

                DatePicker::make('valid_from')
                    ->required()
                    ->native(false),

                DatePicker::make('valid_until')
                    ->after('valid_from')
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

                TextColumn::make('day_name')
                    ->label('Day')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('day_of_week', $direction)),

                TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->options(DrawDocument::getDayNames())
                    ->label('Day'),

                Filter::make('current')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('valid_from', '<=', now())
                        ->where(function ($query) {
                            $query->where('valid_until', '>=', now())
                                ->orWhereNull('valid_until');
                        }))
                    ->label('Current Draws Only')
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View PDF')
                    ->icon('heroicon-m-eye')
                    ->url(fn (DrawDocument $record): string => $record->getFileUrl())
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->visible(fn () => Auth::user()->hasAnyRole(['admin', 'staff'])),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()->hasAnyRole(['admin', 'staff']))
                    ->before(function (DrawDocument $record) {
                        Storage::disk('public')->delete($record->file_path);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasAnyRole(['admin', 'staff']))
                        ->before(function (Collection $records) {
                            $records->each(function ($record) {
                                Storage::disk('public')->delete($record->file_path);
                            });
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrawDocuments::route('/'),
            'create' => CreateDrawDocument::route('/create'),
            'edit' => EditDrawDocument::route('/{record}/edit'),
        ];
    }
}
