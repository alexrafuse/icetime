<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\Permission;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\SpareAvailability;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SpareAvailabilityResource\Pages;

class SpareAvailabilityResource extends Resource
{
    protected static ?string $model = SpareAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Members Area';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Spare List';
    protected static ?string $heading = 'Spare List';

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->visible(fn () => auth()->user()->can('manage spares')),
                Forms\Components\Grid::make(5)
                    ->schema([
                        Forms\Components\Toggle::make('monday')
                            ->inline(false),
                        Forms\Components\Toggle::make('tuesday')
                            ->inline(false),
                        Forms\Components\Toggle::make('wednesday')
                            ->inline(false),
                        Forms\Components\Toggle::make('thursday')
                            ->inline(false),
                        Forms\Components\Toggle::make('friday')
                            ->inline(false),
                    ]),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->nullable(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('sms_enabled')
                            ->label('Available via SMS')
                            ->inline(false),
                        Forms\Components\Toggle::make('call_enabled')
                            ->label('Available via Phone Call')
                            ->inline(false),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (!auth()->user()->can('manage spares')) {
                    $query->where('user_id', auth()->id());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('monday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('tuesday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('wednesday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('thursday')
                    ->boolean(),
                Tables\Columns\IconColumn::make('friday')
                    ->boolean(),
                Tables\Columns\TextColumn::make('phone_number'),
                Tables\Columns\IconColumn::make('sms_enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('call_enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('days')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['values'])) {
                            foreach ($data['values'] as $day) {
                                $query->where($day, true);
                            }
                        }
                    })
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpareAvailabilities::route('/'),
            'create' => Pages\CreateSpareAvailability::route('/create'),
            'edit' => Pages\EditSpareAvailability::route('/{record}/edit'),
        ];
    }

   
} 