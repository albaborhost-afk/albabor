<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ListingResource;
use App\Models\Listing;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Vues par annonce, la plus consultée en premier.
 *
 * `views_count` est le cumul depuis la publication ; la colonne « 7 jours »
 * vient de la table des vues et montre ce qui est vivant maintenant — une
 * vieille annonce peut avoir un gros total mais ne plus intéresser personne.
 */
class TopListingsByViewsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Annonces les plus vues')
            ->description('Vues cumulées et activité des 7 derniers jours')
            ->headerActions([
                Tables\Actions\Action::make('allListings')
                    ->label('Toutes les annonces')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.listings.index'))
                    ->color('gray')
                    ->size('sm'),
            ])
            ->query(
                Listing::query()
                    ->with('user')
                    ->withCount(['views as views_last_7_days' => fn (Builder $q) => $q->where('view_date', '>=', now()->subDays(6)->toDateString())])
            )
            ->defaultSort('views_count', 'desc')
            ->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Annonce')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn (Listing $record) => $record->title)
                    ->weight('medium')
                    ->color('primary')
                    ->url(fn (Listing $record) => ListingResource::getUrl('edit', ['record' => $record])),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendeur')
                    ->limit(20)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Catégorie')
                    ->colors([
                        'primary' => 'boat',
                        'info'    => 'jetski',
                        'warning' => 'engine',
                        'gray'    => 'parts',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'boat'   => 'Bateau',
                        'jetski' => 'Jet-ski',
                        'engine' => 'Moteur',
                        'parts'  => 'Pièces',
                        default  => $state,
                    })
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending_review',
                        'danger'  => 'rejected',
                        'gray'    => fn ($state) => in_array($state, ['expired', 'paused', 'draft', 'sold', 'awaiting_payment']),
                    ])
                    ->formatStateUsing(fn (Listing $record): string => $record->status_label)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues (total)')
                    ->alignCenter()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state, 0, ',', ' ')),

                Tables\Columns\TextColumn::make('views_last_7_days')
                    ->label('Vues (7 j)')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('favorites_count')
                    ->label('Favoris')
                    ->alignCenter()
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'boat'   => 'Bateau',
                        'jetski' => 'Jet-ski',
                        'engine' => 'Moteur',
                        'parts'  => 'Pièces détachées',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active'         => 'Active',
                        'pending_review' => 'En attente de validation',
                        'sold'           => 'Vendue',
                        'expired'        => 'Expirée',
                        'paused'         => 'Suspendue',
                    ]),
            ])
            ->emptyStateHeading('Aucune annonce')
            ->emptyStateIcon('heroicon-o-eye');
    }
}
