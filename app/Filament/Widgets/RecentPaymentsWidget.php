<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Paiements recents')
            ->description('Les 5 derniers paiements')
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('Voir tout')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.payments.index'))
                    ->color('gray')
                    ->size('sm'),
            ])
            ->query(
                Payment::query()
                    ->with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->limit(12)
                    ->weight('medium')
                    ->tooltip(fn (Payment $record) => $record->user?->name)
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount_dzd')
                    ->label('Montant')
                    ->money('DZD')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Attente',
                        'approved' => 'OK',
                        'rejected' => 'Refuse',
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
                    ->url(fn (Payment $record) => route('filament.admin.resources.payments.edit', $record))
                    ->color('gray'),
            ])
            ->striped()
            ->paginated(false);
    }
}
