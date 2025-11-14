<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\UserActivityResource\Pages;
use App\UserActivity;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class UserActivityResource extends Resource
{
    protected static ?string $model = UserActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'User Activity';

    protected static ?string $modelLabel = 'User Activity';

    protected static ?string $pluralModelLabel = 'User Activities';

    public static function canViewAny(): bool
    {
        return auth()->user()->can(Permission::VIEW_MEMBERSHIPS->value);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['name', 'first_name', 'last_name', 'email'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('active_at')
                    ->label('Last Active')
                    ->dateTime()
                    ->sortable()
                    ->description(fn (UserActivity $record): string => $record->active_at->diffForHumans()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('active_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('active_at', '>=', now()->startOfDay())),

                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereDate('active_at', '>=', now()->startOfWeek())),

                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query->whereDate('active_at', '>=', now()->startOfMonth())),

                Tables\Filters\Filter::make('last_30_days')
                    ->label('Last 30 Days')
                    ->query(fn (Builder $query): Builder => $query->whereDate('active_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for read-only resource
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
            'index' => Pages\ListUserActivities::route('/'),
        ];
    }
}
