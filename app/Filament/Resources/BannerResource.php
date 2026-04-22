<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Bannières publicitaires';

    protected static ?string $modelLabel = 'Bannière';

    protected static ?string $pluralModelLabel = 'Bannières';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subtitle')
                            ->label('Sous-titre')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->disk(config('filesystems.listing_disk', 'public'))
                            ->directory('banners')
                            ->imageEditor()
                            ->maxSize(15360)
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_url')
                            ->label('Lien (URL)')
                            ->url()
                            ->nullable()
                            ->helperText('Optionnel. Ex: https://example.com')
                            ->maxLength(2048),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Nom de l\'entreprise')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Diffusion')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Position')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Début de diffusion')
                            ->timezone('Africa/Algiers')
                            ->native(false)
                            ->nullable()
                            ->helperText('Optionnel. Laisser vide pour activer immédiatement.'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin de diffusion')
                            ->timezone('Africa/Algiers')
                            ->native(false)
                            ->nullable()
                            ->helperText('Optionnel. Laisser vide pour ne jamais expirer.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk(config('filesystems.listing_disk', 'public'))
                    ->square()
                    ->size(60),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Entreprise')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Vues')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clics')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i', 'Africa/Algiers')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i', 'Africa/Algiers')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('position', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\Action::make('clearSchedule')
                    ->label('Toujours actif')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Rendre toujours actif ?')
                    ->modalDescription('Efface les dates de début et de fin pour que la bannière soit affichée en permanence.')
                    ->visible(fn (Banner $record): bool => $record->starts_at !== null || $record->ends_at !== null)
                    ->action(fn (Banner $record) => $record->update(['starts_at' => null, 'ends_at' => null])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
