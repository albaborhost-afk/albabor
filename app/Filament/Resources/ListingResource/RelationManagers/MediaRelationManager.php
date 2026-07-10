<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use App\Models\Listing;
use App\Services\ListingMediaStorage;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Photos';

    protected static ?string $icon = 'heroicon-o-photo';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->media()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Photos de l\'annonce')
            ->description('Glissez-déposez les lignes pour réorganiser — la première photo est la couverture de l\'annonce.')
            ->defaultSort('order')
            ->reorderable('order')
            ->paginated(false)
            ->columns([
                Tables\Columns\ImageColumn::make('apercu')
                    ->label('Aperçu')
                    ->getStateUsing(fn ($record) => route('listing-media.show', ['media' => $record->id, 'variant' => 'thumb']))
                    ->size(72)
                    ->square()
                    ->extraImgAttributes(['style' => 'border-radius: 8px; object-fit: cover;']),
                Tables\Columns\TextColumn::make('order')
                    ->label('Position')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('couverture')
                    ->label('Couverture')
                    ->getStateUsing(fn ($record, $livewire) => $record->order === $livewire->getOwnerRecord()->media()->min('order'))
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajoutée le')
                    ->dateTime('d/m/Y H:i')
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('addPhotos')
                    ->label('Ajouter des photos')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Ajouter des photos')
                    ->modalDescription('Les photos sont redimensionnées et reçoivent le filigrane Albabor automatiquement.')
                    ->modalSubmitActionLabel('Téléverser')
                    ->form(fn ($livewire) => [
                        Forms\Components\FileUpload::make('photos')
                            ->label('Photos')
                            ->helperText('Encore ' . app(ListingMediaStorage::class)->slotsLeft($livewire->getOwnerRecord()) . ' emplacement(s) sur ' . Listing::MAX_IMAGES . ' (JPEG, PNG, WebP — max 15 Mo chacune).')
                            ->multiple()
                            ->image()
                            ->required()
                            ->maxFiles(max(1, app(ListingMediaStorage::class)->slotsLeft($livewire->getOwnerRecord())))
                            ->maxSize(Listing::MAX_IMAGE_SIZE_KB)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'])
                            ->disk('local')
                            ->directory('tmp-listing-uploads')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $listing = $livewire->getOwnerRecord();
                        $storage = app(ListingMediaStorage::class);

                        $files = array_slice($data['photos'] ?? [], 0, $storage->slotsLeft($listing));
                        $saved = 0;

                        foreach ($files as $tmpPath) {
                            if (!$tmpPath || !Storage::disk('local')->exists($tmpPath)) {
                                continue;
                            }

                            if ($storage->store($listing, Storage::disk('local')->path($tmpPath))) {
                                $saved++;
                            }

                            Storage::disk('local')->delete($tmpPath);
                        }

                        Notification::make()
                            ->title($saved . ' photo(s) ajoutée(s)')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('makeCover')
                    ->label('Mettre en couverture')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn ($record, $livewire) => $record->order !== $livewire->getOwnerRecord()->media()->min('order'))
                    ->action(function ($record) {
                        app(ListingMediaStorage::class)->moveToFront($record);

                        Notification::make()
                            ->title('Photo définie comme couverture')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('listing-media.show', ['media' => $record->id]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading('Supprimer cette photo ?')
                    ->modalDescription('La photo et sa miniature seront définitivement supprimées.')
                    ->using(fn ($record) => app(ListingMediaStorage::class)->delete($record)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Supprimer la sélection')
                    ->modalHeading('Supprimer les photos sélectionnées ?')
                    ->using(fn ($records) => $records->each(
                        fn ($record) => app(ListingMediaStorage::class)->delete($record)
                    )),
            ])
            ->emptyStateHeading('Aucune photo')
            ->emptyStateDescription('Ajoutez des photos avec le bouton « Ajouter des photos » ci-dessus.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
