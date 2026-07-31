<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Qui a payé, et combien au total.
 *
 * La liste des paiements montre les transactions une par une ; ici on voit
 * le client derrière — utile pour savoir qui fait vivre la plateforme.
 * Seuls les paiements validés sont comptés : un paiement en attente n'est
 * pas un revenu.
 */
class TopPayingUsersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Clients ayant payé')
            ->description('Total encaissé par compte — paiements validés uniquement')
            ->headerActions([
                Tables\Actions\Action::make('allPayments')
                    ->label('Tous les paiements')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.payments.index'))
                    ->color('gray')
                    ->size('sm'),
            ])
            ->query(
                User::query()
                    ->withCount(['payments as approved_payments_count' => fn (Builder $q) => $q->where('status', 'approved')])
                    ->withSum(['payments as total_paid' => fn (Builder $q) => $q->where('status', 'approved')], 'amount_dzd')
                    ->withMax(['payments as last_payment_at' => fn (Builder $q) => $q->where('status', 'approved')], 'created_at')
                    ->whereHas('payments', fn (Builder $q) => $q->where('status', 'approved'))
            )
            ->defaultSort('total_paid', 'desc')
            ->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->color('primary')
                    ->url(fn (User $record) => UserResource::getUrl('edit', ['record' => $record])),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->formatStateUsing(fn (?string $state, User $record): string => $state
                        ? trim(($record->phone_country_code ?? '') . ' ' . $state)
                        : '—')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('account_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'user',
                        'success' => 'vendor',
                        'danger'  => 'admin',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user'   => 'Utilisateur',
                        'vendor' => 'Vendeur',
                        'admin'  => 'Admin',
                        default  => $state,
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approved_payments_count')
                    ->label('Paiements')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Total encaissé')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state, 0, ',', ' ') . ' DA')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('last_payment_at')
                    ->label('Dernier paiement')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color('gray'),
            ])
            ->emptyStateHeading('Aucun paiement validé')
            ->emptyStateDescription('Les clients apparaîtront ici dès qu\'un paiement sera approuvé.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
