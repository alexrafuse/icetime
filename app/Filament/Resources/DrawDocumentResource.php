<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\DrawDocument;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DrawDocumentResource\Pages\EditDrawDocument;
use App\Filament\Resources\DrawDocumentResource\Pages\ListDrawDocuments;
use App\Filament\Resources\DrawDocumentResource\Pages\CreateDrawDocument;

final class DrawDocumentResource extends Resource
{
    protected static ?string $model = DrawDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('day_of_week')
                    ->options(DrawDocument::getDayNames())
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('PDF File')
                    ->directory('draws')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->downloadable()
                    ->openable(),

                Forms\Components\DatePicker::make('valid_from')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('valid_until')
                    ->after('valid_from')
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_name')
                    ->label('Day')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('day_of_week', $direction)),

                Tables\Columns\TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->options(DrawDocument::getDayNames())
                    ->label('Day'),

                Tables\Filters\Filter::make('current')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('valid_from', '<=', now())
                        ->where(function ($query) {
                            $query->where('valid_until', '>=', now())
                                ->orWhereNull('valid_until');
                        }))
                    ->label('Current Draws Only')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View PDF')
                    ->icon('heroicon-m-eye')
                    ->url(fn (DrawDocument $record): string => $record->getFileUrl())
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (DrawDocument $record) {
                        // Delete the file when deleting the record
                        Storage::disk('public')->delete($record->file_path);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            // Delete files for all deleted records
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