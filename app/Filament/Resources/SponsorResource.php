<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorResource\Pages\CreateSponsor;
use App\Filament\Resources\SponsorResource\Pages\EditSponsor;
use App\Filament\Resources\SponsorResource\Pages\ListSponsors;
use App\Filament\Resources\SponsorResource\RelationManagers\MembersRelationManager;
use App\Filament\Resources\SponsorResource\RelationManagers\SponsorshipsRelationManager;
use App\Filament\Widgets\SponsorshipOverview;
use Domain\Board\Models\Sponsor;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class SponsorResource extends Resource
{
    protected static ?string $model = Sponsor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Executive';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('contact_name')
                    ->maxLength(255),

                TextInput::make('contact_email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('contact_phone')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('website')
                    ->url()
                    ->maxLength(255),

                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->directory('sponsors')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('225'),

                Textarea::make('notes'),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currentSeasonSponsorship.level.name')
                    ->label('Current Level')
                    ->placeholder('—')
                    ->badge(),

                TextColumn::make('contact_name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('contact_email')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sponsorships_count')
                    ->counts('sponsorships')
                    ->label('Sponsorships'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SponsorshipsRelationManager::class,
            MembersRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SponsorshipOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSponsors::route('/'),
            'create' => CreateSponsor::route('/create'),
            'edit' => EditSponsor::route('/{record}/edit'),
        ];
    }
}
