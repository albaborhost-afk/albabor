<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;

class RecentListingsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 2;

    protected static ?string $heading = null;

    protected function getTableHeading(): string | null
    {
        return null;
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKey();
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Annonces recentes')
            ->description('Les 5 dernieres annonces creees')
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('Voir tout')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.listings.index'))
                    ->color('gray')
                    ->size('sm'),
            ])
            ->query(
                Listing::query()
                    ->with(['user', 'media'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('media.path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => asset('images/placeholder.png'))
                    ->getStateUsing(function (Listing $record) {
                        $media = $record->media->first();
                        return $media ? Storage::url($media->path) : null;
                    })
                    ->size(45),

                Tables\Columns\TextColumn::make('title')
                    ->label('Annonce')
                    ->limit(30)
                    ->weight('medium')
                    ->tooltip(fn (Listing $record) => $record->title)
                    ->description(fn (Listing $record) => $record->user?->name ?? 'Inconnu')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boat' => 'primary',
                        'jetski' => 'success',
                        'engine' => 'warning',
                        'parts' => 'danger',
                        default => 'gray',
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
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'awaiting_payment', 'pending_review' => 'warning',
                        'active' => 'success',
                        'rejected', 'expired', 'paused' => 'danger',
                        'sold' => 'info',
                        default => 'gray',
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->color('gray')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Voir les details')
                    ->url(fn (Listing $record) => route('filament.admin.resources.listings.edit', $record))
                    ->color('gray'),
            ])
            ->striped()
            ->paginated(false);
    }
}
