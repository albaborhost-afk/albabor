<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\VendorListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * "Mes annonces" — espace vendeur.
 * Strictement limité aux annonces pièces / moteurs appartenant au vendeur connecté.
 */
class VendorListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Annonces';

    protected static ?string $navigationLabel = 'Mes annonces';

    protected static ?string $modelLabel = 'annonce';

    protected static ?string $pluralModelLabel = 'annonces';

    protected static ?int $navigationSort = 1;

    /**
     * Le vendeur ne voit QUE ses propres annonces de catégorie moteur / pièces.
     * Aucune annonce d'autrui, aucun bateau ni jet-ski.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->whereIn('category', ['engine', 'parts']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de l\'annonce')
                    ->description('Renseignez les informations principales de votre annonce.')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Titre de votre annonce'),

                        Forms\Components\Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'engine' => 'Moteur',
                                'parts' => 'Pièces détachées',
                            ])
                            ->required()
                            ->native(false)
                            ->default('parts'),

                        Forms\Components\Select::make('etat')
                            ->label('État')
                            ->options(Listing::ETAT_LABELS)
                            ->default('bon_etat')
                            ->required()
                            ->native(false),

                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ]),
                    ]),

                Forms\Components\Section::make('Prix et offre')
                    ->description('Fixez le prix et le type d\'offre.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('price_dzd')
                            ->label('Prix')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('0'),

                        Forms\Components\Select::make('currency')
                            ->label('Devise')
                            ->options([
                                'DZD' => 'DZD - Dinar Algérien',
                                'EUR' => 'EUR - Euro',
                            ])
                            ->default('DZD')
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('type_offre')
                            ->label('Type d\'offre')
                            ->options(Listing::TYPE_OFFRE_LABELS)
                            ->default('negociable')
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Localisation et média')
                    ->description('Où se trouve l\'article et lien vidéo éventuel.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('wilaya')
                            ->label('Ville / Wilaya')
                            ->maxLength(255)
                            ->placeholder('Ex: Alger, Oran...'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('URL Vidéo YouTube')
                            ->type('url')
                            ->maxLength(500)
                            ->placeholder('https://www.youtube.com/watch?v=...'),
                    ]),

                Forms\Components\Section::make('Contact')
                    ->description('Coordonnées affichées aux acheteurs intéressés.')
                    ->icon('heroicon-o-phone')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('numero_whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->placeholder('+213 XXX XXX XXX'),

                        Forms\Components\TextInput::make('numero_mobile')
                            ->label('Mobile')
                            ->tel()
                            ->placeholder('+213 XXX XXX XXX'),

                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email de contact')
                            ->email()
                            ->placeholder('contact@email.com'),

                        Forms\Components\Toggle::make('mediation_enabled')
                            ->label('Activer la médiation')
                            ->helperText('Si activée, vos numéros de contact sont masqués et les acheteurs passent par la médiation Albabor.')
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Validation')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('moderation_notice')
                            ->label('')
                            ->content('Toute annonce créée ou modifiée est soumise à la modération de l\'équipe Albabor avant d\'être publiée. Elle restera "En attente de validation" tant qu\'un administrateur ne l\'a pas approuvée.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image')
                    ->label('')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl(url('/images/placeholder-listing.png'))
                    ->getStateUsing(function (Listing $record) {
                        $media = $record->media->first();
                        if (! $media) {
                            return null;
                        }
                        $disk = config('filesystems.listing_disk', 'public');
                        $path = $media->thumbnail_path ?? $media->path;
                        try {
                            return Storage::disk($disk)->url($path);
                        } catch (\Throwable) {
                            return route('listing-media.show', ['media' => $media->id]);
                        }
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn (Listing $record) => $record->title),

                Tables\Columns\TextColumn::make('category_label')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (Listing $record): string => match ($record->category) {
                        'engine' => 'warning',
                        'parts' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('formatted_price')
                    ->label('Prix')
                    ->color('success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (Listing $record): string => $record->status_label)
                    ->color(fn (Listing $record): string => match ($record->status_color) {
                        'green' => 'success',
                        'red' => 'danger',
                        'blue' => 'info',
                        'yellow', 'orange' => 'warning',
                        'purple' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('favorites_count')
                    ->label('Favoris')
                    ->icon('heroicon-o-heart')
                    ->color('danger')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'awaiting_payment' => 'En attente de paiement',
                        'pending_review' => 'En attente de validation',
                        'active' => 'Active',
                        'rejected' => 'Refusée',
                        'sold' => 'Vendue',
                        'expired' => 'Expirée',
                        'paused' => 'Suspendue',
                    ])
                    ->indicator('Statut'),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'engine' => 'Moteur',
                        'parts' => 'Pièces détachées',
                    ])
                    ->indicator('Catégorie'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nouvelle annonce'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier'),

                Tables\Actions\Action::make('mark_sold')
                    ->label('Marquer vendue')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary')
                    ->visible(fn (Listing $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Marquer comme vendue')
                    ->modalDescription('Cette annonce sera marquée comme vendue et ne sera plus visible.')
                    ->action(function (Listing $record) {
                        $record->update(['status' => 'sold']);
                        Notification::make()
                            ->title('Annonce marquée comme vendue')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('pause')
                    ->label('Mettre en pause')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(fn (Listing $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Mettre l\'annonce en pause')
                    ->modalDescription('L\'annonce sera suspendue et masquée temporairement.')
                    ->action(function (Listing $record) {
                        $record->update(['status' => 'paused']);
                        Notification::make()
                            ->title('Annonce mise en pause')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reactivate')
                    ->label('Réactiver')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn (Listing $record) => $record->status === 'paused')
                    ->requiresConfirmation()
                    ->modalHeading('Réactiver l\'annonce')
                    ->modalDescription('L\'annonce sera de nouveau visible.')
                    ->action(function (Listing $record) {
                        $record->update(['status' => 'active']);
                        Notification::make()
                            ->title('Annonce réactivée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->emptyStateHeading('Aucune annonce')
            ->emptyStateDescription('Vous n\'avez pas encore publié d\'annonce de moteur ou de pièce détachée.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorListings::route('/'),
            'create' => Pages\CreateVendorListing::route('/create'),
            'edit' => Pages\EditVendorListing::route('/{record}/edit'),
        ];
    }
}
