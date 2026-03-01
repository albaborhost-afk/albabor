<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Annonces';
    protected static ?string $modelLabel = 'Annonce';
    protected static ?string $pluralModelLabel = 'Annonces';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Gestion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Annonce')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Section::make('Annonce')
                                            ->description('Informations principales de l\'annonce')
                                            ->icon('heroicon-o-document-text')
                                            ->columnSpan(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Titre')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull()
                                                    ->placeholder('Titre de l\'annonce'),
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
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('price_dzd')
                                                            ->label('Prix')
                                                            ->numeric()
                                                            ->required()
                                                            ->placeholder('0'),
                                                        Forms\Components\Select::make('currency')
                                                            ->label('Devise')
                                                            ->options([
                                                                'DZD' => 'DZD - Dinar Algerien',
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
                                                Forms\Components\Select::make('remarque_echange')
                                                    ->label('Echange')
                                                    ->options(Listing::REMARQUE_ECHANGE_LABELS)
                                                    ->placeholder('Non precise')
                                                    ->native(false),
                                            ]),

                                        Forms\Components\Section::make('Classification')
                                            ->description('Categorie et localisation')
                                            ->icon('heroicon-o-tag')
                                            ->columnSpan(1)
                                            ->schema([
                                                Forms\Components\Select::make('user_id')
                                                    ->relationship('user', 'name')
                                                    ->label('Vendeur')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nom')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('email')
                                                            ->label('Email')
                                                            ->email()
                                                            ->required(),
                                                    ]),
                                                Forms\Components\Select::make('category')
                                                    ->label('Categorie')
                                                    ->options([
                                                        'boat' => 'Bateau',
                                                        'jetski' => 'Jet-ski',
                                                        'engine' => 'Moteur',
                                                        'parts' => 'Pieces detachees',
                                                    ])
                                                    ->required()
                                                    ->native(false)
                                                    ->live(),
                                                Forms\Components\Select::make('etat')
                                                    ->label('Etat')
                                                    ->options(Listing::ETAT_LABELS)
                                                    ->default('bon_etat')
                                                    ->required()
                                                    ->native(false),
                                                Forms\Components\TextInput::make('wilaya')
                                                    ->label('Wilaya')
                                                    ->required()
                                                    ->placeholder('Ex: Alger, Oran...'),
                                                Forms\Components\TextInput::make('visible_a')
                                                    ->label('Visible a')
                                                    ->placeholder('Ex: Alger, Oran, Annaba...'),
                                                Forms\Components\TextInput::make('pays')
                                                    ->label('Pays')
                                                    ->default('Algerie')
                                                    ->placeholder('Algerie'),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Contact du vendeur')
                                    ->description('Informations de contact affichees sur l\'annonce')
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
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Specifications')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                // General specs (all categories except parts)
                                Forms\Components\Section::make('Informations generales')
                                    ->description('Modele, fabricant et identification')
                                    ->icon('heroicon-o-wrench-screwdriver')
                                    ->visible(fn ($get) => in_array($get('category'), ['boat', 'jetski', 'engine']))
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.general.modele')
                                            ->label('Modele'),
                                        Forms\Components\TextInput::make('specs.general.fabricant')
                                            ->label('Fabricant'),
                                        Forms\Components\TextInput::make('specs.general.annee_construction')
                                            ->label('Annee de construction')
                                            ->numeric()
                                            ->minValue(1900)
                                            ->maxValue(2030),
                                        Forms\Components\Select::make('specs.general.immatriculation')
                                            ->label('Immatriculation')
                                            ->options(array_combine(Listing::IMMATRICULATION_OPTIONS, Listing::IMMATRICULATION_OPTIONS))
                                            ->placeholder('Selectionner...')
                                            ->native(false),
                                    ]),

                                // Dimensions (boat/jetski)
                                Forms\Components\Section::make('Dimensions')
                                    ->description('Mesures de l\'embarcation')
                                    ->icon('heroicon-o-arrows-pointing-out')
                                    ->visible(fn ($get) => in_array($get('category'), ['boat', 'jetski']))
                                    ->columns(5)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.dimensions.longueur')
                                            ->label('Longueur (m)')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('specs.dimensions.largeur')
                                            ->label('Largeur (m)')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('specs.dimensions.tirant_eau')
                                            ->label('Tirant d\'eau (m)')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('specs.dimensions.tirant_air')
                                            ->label('Tirant d\'air (m)')
                                            ->numeric()
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('specs.dimensions.tonnage')
                                            ->label('Tonnage (t)')
                                            ->numeric()
                                            ->step(0.01),
                                    ]),

                                // Motorisation (boat/jetski/engine)
                                Forms\Components\Section::make('Motorisation')
                                    ->description('Details du moteur')
                                    ->icon('heroicon-o-bolt')
                                    ->visible(fn ($get) => in_array($get('category'), ['boat', 'jetski', 'engine']))
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.motorisation.marque_moteur')
                                            ->label('Marque moteur'),
                                        Forms\Components\Select::make('specs.motorisation.propulsion')
                                            ->label('Propulsion')
                                            ->options(array_combine(Listing::PROPULSION_OPTIONS, Listing::PROPULSION_OPTIONS))
                                            ->placeholder('Selectionner...')
                                            ->native(false),
                                        Forms\Components\Select::make('specs.motorisation.type_carburant')
                                            ->label('Carburant')
                                            ->options(array_combine(Listing::CARBURANT_OPTIONS, Listing::CARBURANT_OPTIONS))
                                            ->placeholder('Selectionner...')
                                            ->native(false),
                                        Forms\Components\TextInput::make('specs.motorisation.nombre_moteurs')
                                            ->label('Nombre de moteurs')
                                            ->numeric()
                                            ->default(1),
                                        Forms\Components\TextInput::make('specs.motorisation.puissance_par_moteur')
                                            ->label('Puissance / moteur (CV)')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.motorisation.puissance_totale')
                                            ->label('Puissance totale (CV)')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.motorisation.nombre_heures')
                                            ->label('Heures de fonctionnement')
                                            ->numeric(),
                                    ]),

                                // Reservoirs (boat only)
                                Forms\Components\Section::make('Reservoirs')
                                    ->description('Capacites des reservoirs')
                                    ->icon('heroicon-o-beaker')
                                    ->visible(fn ($get) => $get('category') === 'boat')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.reservoirs.reservoir_carburant')
                                            ->label('Reservoir carburant (L)')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.reservoirs.reservoir_eau_douce')
                                            ->label('Eau douce (L)')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.reservoirs.stockage')
                                            ->label('Stockage (L)')
                                            ->numeric(),
                                    ]),

                                // Amenagements (boat only)
                                Forms\Components\Section::make('Amenagements')
                                    ->description('Amenagements interieurs')
                                    ->icon('heroicon-o-home')
                                    ->visible(fn ($get) => $get('category') === 'boat')
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.amenagements.nombre_couchettes')
                                            ->label('Couchettes')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.amenagements.nombre_cabines')
                                            ->label('Cabines')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.amenagements.nombre_sanitaire')
                                            ->label('Sanitaires')
                                            ->numeric(),
                                        Forms\Components\TextInput::make('specs.amenagements.nombre_cuisine')
                                            ->label('Cuisines')
                                            ->numeric(),
                                    ]),

                                // Tags / Equipment (boat/jetski)
                                Forms\Components\Section::make('Equipements et options')
                                    ->description('Equipements, options et electronique embarquee')
                                    ->icon('heroicon-o-cog-8-tooth')
                                    ->visible(fn ($get) => in_array($get('category'), ['boat', 'jetski']))
                                    ->schema([
                                        Forms\Components\TagsInput::make('specs.tags.equipement')
                                            ->label('Equipement')
                                            ->placeholder('Ajouter un equipement...')
                                            ->separator(','),
                                        Forms\Components\TagsInput::make('specs.tags.options')
                                            ->label('Options')
                                            ->placeholder('Ajouter une option...')
                                            ->separator(','),
                                        Forms\Components\TagsInput::make('specs.tags.electronique')
                                            ->label('Electronique')
                                            ->placeholder('Ajouter un equipement electronique...')
                                            ->separator(','),
                                    ]),

                                // Extras (boat/jetski)
                                Forms\Components\Section::make('Extras')
                                    ->description('Annexe, remorque et port')
                                    ->icon('heroicon-o-plus-circle')
                                    ->visible(fn ($get) => in_array($get('category'), ['boat', 'jetski']))
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Toggle::make('specs.extras.annexe')
                                            ->label('Annexe incluse')
                                            ->inline(false),
                                        Forms\Components\Toggle::make('specs.extras.remorque')
                                            ->label('Remorque incluse')
                                            ->inline(false)
                                            ->live(),
                                        Forms\Components\TextInput::make('specs.extras.marque_remorque')
                                            ->label('Marque de la remorque')
                                            ->visible(fn ($get) => $get('specs.extras.remorque')),
                                        Forms\Components\Toggle::make('specs.extras.place_au_port')
                                            ->label('Place au port')
                                            ->inline(false)
                                            ->live(),
                                        Forms\Components\TextInput::make('specs.extras.longueur_place')
                                            ->label('Longueur place (m)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->visible(fn ($get) => $get('specs.extras.place_au_port')),
                                        Forms\Components\TextInput::make('specs.extras.largeur_place')
                                            ->label('Largeur place (m)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->visible(fn ($get) => $get('specs.extras.place_au_port')),
                                        Forms\Components\TextInput::make('specs.extras.adresse_port')
                                            ->label('Adresse du port')
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => $get('specs.extras.place_au_port')),
                                    ]),

                                // Parts specifications
                                Forms\Components\Section::make('Specifications Pieces')
                                    ->description('Details de la piece detachee')
                                    ->icon('heroicon-o-wrench-screwdriver')
                                    ->visible(fn ($get) => $get('category') === 'parts')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('specs.general.fabricant')
                                            ->label('Marque'),
                                        Forms\Components\TextInput::make('specs.general.part_number')
                                            ->label('Reference'),
                                        Forms\Components\TextInput::make('specs.general.compatible_with')
                                            ->label('Compatible avec'),
                                        Forms\Components\Select::make('specs.general.part_type')
                                            ->label('Type de piece')
                                            ->options([
                                                'electrical' => 'Electrique',
                                                'mechanical' => 'Mecanique',
                                                'body' => 'Carrosserie',
                                                'accessories' => 'Accessoires',
                                                'other' => 'Autre',
                                            ])
                                            ->native(false),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Medias')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('Images de l\'annonce')
                                    ->description('Galerie d\'images (max 10)')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        Forms\Components\Placeholder::make('media_gallery')
                                            ->label('Images actuelles')
                                            ->content(function ($record) {
                                                if (!$record || $record->media->isEmpty()) {
                                                    return 'Aucune image';
                                                }

                                                $html = '<div style="display: flex; flex-wrap: wrap; gap: 12px;">';
                                                foreach ($record->media as $media) {
                                                    $url = route('listing-media.show', ['media' => $media->id]);
                                                    $html .= '<div style="position: relative;">';
                                                    $html .= '<img src="' . $url . '" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;" />';
                                                    $html .= '<span style="position: absolute; top: 4px; left: 4px; background: rgba(0,0,0,0.6); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px;">#' . ($media->order + 1) . '</span>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpanFull(),
                                        Forms\Components\Placeholder::make('media_info')
                                            ->label('')
                                            ->content('Pour modifier les images, utilisez l\'application mobile ou l\'API.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Statut')
                            ->icon('heroicon-o-flag')
                            ->badge(fn ($record) => $record?->status === 'pending_review' ? '!' : null)
                            ->badgeColor('warning')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Section::make('Etat de l\'annonce')
                                            ->description('Statut et moderation')
                                            ->icon('heroicon-o-check-badge')
                                            ->schema([
                                                Forms\Components\Select::make('status')
                                                    ->label('Statut')
                                                    ->options([
                                                        'draft' => 'Brouillon',
                                                        'awaiting_payment' => 'En attente de paiement',
                                                        'pending_review' => 'En attente de validation',
                                                        'active' => 'Active',
                                                        'rejected' => 'Refusee',
                                                        'sold' => 'Vendue',
                                                        'expired' => 'Expiree',
                                                        'paused' => 'Suspendue',
                                                    ])
                                                    ->required()
                                                    ->native(false)
                                                    ->live(),
                                                Forms\Components\Textarea::make('rejection_reason')
                                                    ->label('Raison du refus')
                                                    ->visible(fn ($get) => $get('status') === 'rejected')
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                                Forms\Components\Toggle::make('mediation_enabled')
                                                    ->label('Mediation activee')
                                                    ->helperText('Si active, le numero du vendeur sera masque')
                                                    ->inline(false),
                                            ]),

                                        Forms\Components\Section::make('Dates de publication')
                                            ->description('Periodes de visibilite')
                                            ->icon('heroicon-o-calendar')
                                            ->schema([
                                                Forms\Components\DateTimePicker::make('published_until')
                                                    ->label('Publie jusqu\'au')
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->native(false),
                                                Forms\Components\DateTimePicker::make('featured_until')
                                                    ->label('En vedette jusqu\'au')
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->native(false)
                                                    ->helperText('Laissez vide si non mise en avant'),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Statistiques')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Section::make('Performance')
                                    ->description('Statistiques de l\'annonce')
                                    ->icon('heroicon-o-presentation-chart-line')
                                    ->schema([
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('views_stat')
                                                    ->label('Vues')
                                                    ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                        '<div style="font-size: 2rem; font-weight: bold; color: #3b82f6;">' .
                                                        number_format($record?->views_count ?? 0) .
                                                        '</div>'
                                                    )),
                                                Forms\Components\Placeholder::make('favorites_stat')
                                                    ->label('Favoris')
                                                    ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                        '<div style="font-size: 2rem; font-weight: bold; color: #ef4444;">' .
                                                        number_format($record?->favorites_count ?? 0) .
                                                        '</div>'
                                                    )),
                                                Forms\Components\Placeholder::make('created_stat')
                                                    ->label('Creee le')
                                                    ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i') ?? '-'),
                                                Forms\Components\Placeholder::make('updated_stat')
                                                    ->label('Modifiee le')
                                                    ->content(fn ($record) => $record?->updated_at?->format('d/m/Y H:i') ?? '-'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
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
                        return $media ? route('listing-media.show', ['media' => $media->id]) : null;
                    }),

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight(FontWeight::SemiBold)
                    ->tooltip(fn (Listing $record) => $record->title)
                    ->description(fn (Listing $record): string => $record->user?->name ?? 'N/A'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boat' => 'info',
                        'jetski' => 'success',
                        'engine' => 'warning',
                        'parts' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'boat' => 'heroicon-o-lifebuoy',
                        'jetski' => 'heroicon-o-bolt',
                        'engine' => 'heroicon-o-cog-6-tooth',
                        'parts' => 'heroicon-o-wrench-screwdriver',
                        default => 'heroicon-o-tag',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'boat' => 'Bateau',
                        'jetski' => 'Jet-ski',
                        'engine' => 'Moteur',
                        'parts' => 'Pieces',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('price_dzd')
                    ->label('Prix')
                    ->formatStateUsing(function ($state, $record) {
                        $symbol = $record->currency === 'EUR' ? '€' : 'DA';
                        return number_format($state, 0, ',', ' ') . ' ' . $symbol;
                    })
                    ->description(fn ($record) => $record->formatted_converted_price)
                    ->sortable()
                    ->color('success')
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('type_offre')
                    ->label('Offre')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'negociable' => 'info',
                        'fix' => 'gray',
                        'offert' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => Listing::TYPE_OFFRE_LABELS[$state] ?? ($state ?? '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('wilaya')
                    ->label('Wilaya')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('etat')
                    ->label('Etat')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'jamais_utilise' => 'success',
                        'comme_neuf' => 'info',
                        'bon_etat' => 'primary',
                        'etat_moyen' => 'warning',
                        'a_reviser' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'jamais_utilise' => 'Neuf',
                        'comme_neuf' => 'Comme Neuf',
                        'bon_etat' => 'Bon Etat',
                        'etat_moyen' => 'Moyen',
                        'a_reviser' => 'A reviser',
                        default => $state ?? '-',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'awaiting_payment' => 'warning',
                        'pending_review' => 'info',
                        'active' => 'success',
                        'rejected' => 'danger',
                        'sold' => 'primary',
                        'expired' => 'gray',
                        'paused' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil',
                        'awaiting_payment' => 'heroicon-o-credit-card',
                        'pending_review' => 'heroicon-o-clock',
                        'active' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        'sold' => 'heroicon-o-shopping-bag',
                        'expired' => 'heroicon-o-calendar-days',
                        'paused' => 'heroicon-o-pause-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'awaiting_payment' => 'Paiement',
                        'pending_review' => 'Validation',
                        'active' => 'Active',
                        'rejected' => 'Refusee',
                        'sold' => 'Vendue',
                        'expired' => 'Expiree',
                        'paused' => 'Suspendue',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('featured')
                    ->label('Vedette')
                    ->boolean()
                    ->getStateUsing(fn (Listing $record) => $record->isFeatured())
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues')
                    ->sortable()
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('favorites_count')
                    ->label('Favoris')
                    ->sortable()
                    ->icon('heroicon-o-heart')
                    ->color('danger')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->multiple()
                    ->options([
                        'draft' => 'Brouillon',
                        'awaiting_payment' => 'En attente de paiement',
                        'pending_review' => 'En attente de validation',
                        'active' => 'Active',
                        'rejected' => 'Refusee',
                        'sold' => 'Vendue',
                        'expired' => 'Expiree',
                        'paused' => 'Suspendue',
                    ])
                    ->indicator('Statut'),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Categorie')
                    ->multiple()
                    ->options([
                        'boat' => 'Bateau',
                        'jetski' => 'Jet-ski',
                        'engine' => 'Moteur',
                        'parts' => 'Pieces detachees',
                    ])
                    ->indicator('Categorie'),

                Tables\Filters\SelectFilter::make('etat')
                    ->label('Etat')
                    ->multiple()
                    ->options(Listing::ETAT_LABELS)
                    ->indicator('Etat'),

                Tables\Filters\SelectFilter::make('type_offre')
                    ->label('Type d\'offre')
                    ->options(Listing::TYPE_OFFRE_LABELS)
                    ->indicator('Type d\'offre'),

                Tables\Filters\Filter::make('wilaya')
                    ->form([
                        Forms\Components\TextInput::make('wilaya')
                            ->label('Wilaya')
                            ->placeholder('Filtrer par wilaya'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['wilaya'],
                            fn (Builder $query, $wilaya): Builder => $query->where('wilaya', 'like', "%{$wilaya}%")
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['wilaya']) {
                            return null;
                        }
                        return 'Wilaya: ' . $data['wilaya'];
                    }),

                Tables\Filters\Filter::make('featured')
                    ->label('En vedette')
                    ->query(fn (Builder $query): Builder => $query->where('featured_until', '>', now()))
                    ->toggle(),

                Tables\Filters\Filter::make('pending_review')
                    ->label('A valider')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending_review'))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Listing $record) => $record->status === 'pending_review')
                        ->requiresConfirmation()
                        ->modalHeading('Approuver cette annonce')
                        ->modalDescription('L\'annonce sera publiee et visible par tous les utilisateurs.')
                        ->modalSubmitActionLabel('Oui, approuver')
                        ->action(function (Listing $record) {
                            $record->update([
                                'status' => 'active',
                                'published_until' => now()->addDays(365),
                            ]);
                            Notification::make()
                                ->title('Annonce approuvee')
                                ->body('L\'annonce "' . $record->title . '" est maintenant active.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Refuser')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Listing $record) => $record->status === 'pending_review')
                        ->modalHeading('Refuser cette annonce')
                        ->modalDescription('Veuillez indiquer la raison du refus.')
                        ->form([
                            Forms\Components\Select::make('rejection_template')
                                ->label('Modele de refus')
                                ->options([
                                    'incomplete' => 'Informations incompletes',
                                    'inappropriate' => 'Contenu inapproprie',
                                    'duplicate' => 'Annonce en double',
                                    'price' => 'Prix non realiste',
                                    'photos' => 'Photos de mauvaise qualite',
                                    'other' => 'Autre raison',
                                ])
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $templates = [
                                        'incomplete' => 'Votre annonce ne contient pas suffisamment d\'informations. Veuillez completer la description et les caracteristiques.',
                                        'inappropriate' => 'Votre annonce contient du contenu qui ne respecte pas nos conditions d\'utilisation.',
                                        'duplicate' => 'Cette annonce semble etre un doublon d\'une annonce existante.',
                                        'price' => 'Le prix indique ne semble pas correspondre au marche. Veuillez verifier.',
                                        'photos' => 'Les photos fournies ne sont pas de qualite suffisante. Veuillez en ajouter de meilleures.',
                                    ];
                                    if (isset($templates[$state])) {
                                        $set('rejection_reason', $templates[$state]);
                                    }
                                }),
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Raison du refus')
                                ->required()
                                ->rows(4)
                                ->placeholder('Expliquez pourquoi cette annonce est refusee...'),
                        ])
                        ->action(function (Listing $record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            Notification::make()
                                ->title('Annonce refusee')
                                ->body('L\'annonce "' . $record->title . '" a ete refusee.')
                                ->danger()
                                ->send();
                        }),

                    Tables\Actions\Action::make('feature')
                        ->label(fn (Listing $record) => $record->isFeatured() ? 'Retirer vedette' : 'Mettre en avant')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->visible(fn (Listing $record) => $record->status === 'active')
                        ->requiresConfirmation()
                        ->modalHeading(fn (Listing $record) => $record->isFeatured()
                            ? 'Retirer la mise en avant'
                            : 'Mettre en avant cette annonce')
                        ->modalDescription(fn (Listing $record) => $record->isFeatured()
                            ? 'L\'annonce ne sera plus mise en avant.'
                            : 'L\'annonce sera mise en avant pendant 30 jours.')
                        ->action(function (Listing $record) {
                            if ($record->isFeatured()) {
                                $record->update(['featured_until' => null]);
                                Notification::make()
                                    ->title('Mise en avant retiree')
                                    ->success()
                                    ->send();
                            } else {
                                $record->update(['featured_until' => now()->addDays(30)]);
                                Notification::make()
                                    ->title('Annonce mise en avant')
                                    ->body('L\'annonce sera visible en vedette pendant 30 jours.')
                                    ->success()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('mark_sold')
                        ->label('Marquer vendue')
                        ->icon('heroicon-o-shopping-bag')
                        ->color('primary')
                        ->visible(fn (Listing $record) => $record->status === 'active')
                        ->requiresConfirmation()
                        ->modalHeading('Marquer comme vendue')
                        ->modalDescription('Cette annonce sera marquee comme vendue et ne sera plus visible.')
                        ->action(function (Listing $record) {
                            $record->update(['status' => 'sold']);
                            Notification::make()
                                ->title('Annonce marquee comme vendue')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ViewAction::make()
                        ->label('Voir'),

                    Tables\Actions\EditAction::make()
                        ->label('Modifier'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer'),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approuver la selection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending_review') {
                                    $record->update([
                                        'status' => 'active',
                                        'published_until' => now()->addDays(365),
                                    ]);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title($count . ' annonce(s) approuvee(s)')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la selection'),
                ]),
            ])
            ->emptyStateHeading('Aucune annonce')
            ->emptyStateDescription('Aucune annonce ne correspond a vos criteres de recherche.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending_review')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending_review')->count();
        return $count > 5 ? 'danger' : 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'wilaya'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Categorie' => $record->category_label,
            'Prix' => $record->formatted_price,
            'Wilaya' => $record->wilaya,
        ];
    }
}
