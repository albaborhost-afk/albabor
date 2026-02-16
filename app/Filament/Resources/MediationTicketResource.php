<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediationTicketResource\Pages;
use App\Filament\Resources\MediationTicketResource\RelationManagers;
use App\Models\MediationTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediationTicketResource extends Resource
{
    protected static ?string $model = MediationTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Support';

    protected static ?string $modelLabel = 'Ticket de médiation';

    protected static ?string $pluralModelLabel = 'Tickets de médiation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('listing_id')
                    ->relationship('listing', 'title')
                    ->required(),
                Forms\Components\Select::make('buyer_id')
                    ->relationship('buyer', 'name')
                    ->required(),
                Forms\Components\Select::make('seller_id')
                    ->relationship('seller', 'name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'Nouveau',
                        'in_progress' => 'En cours',
                        'awaiting_payment' => 'En attente de paiement',
                        'closed' => 'Fermé',
                        'cancelled' => 'Annulé',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('buyer_message')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('fee_amount_dzd')
                    ->numeric()
                    ->suffix('DA'),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'unpaid' => 'Non payé',
                        'paid' => 'Payé',
                        'waived' => 'Exonéré',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('assigned_admin_id')
                    ->relationship('assignedAdmin', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Annonce')
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->label('Acheteur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->label('Vendeur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'in_progress' => 'warning',
                        'awaiting_payment' => 'warning',
                        'closed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nouveau',
                        'in_progress' => 'En cours',
                        'awaiting_payment' => 'En attente de paiement',
                        'closed' => 'Fermé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_amount_dzd')
                    ->label('Frais')
                    ->numeric()
                    ->suffix(' DA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'waived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Non payé',
                        'paid' => 'Payé',
                        'waived' => 'Exonéré',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedAdmin.name')
                    ->label('Admin assigné')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'new' => 'Nouveau',
                        'in_progress' => 'En cours',
                        'awaiting_payment' => 'En attente de paiement',
                        'closed' => 'Fermé',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'unpaid' => 'Non payé',
                        'paid' => 'Payé',
                        'waived' => 'Exonéré',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMediationTickets::route('/'),
            'create' => Pages\CreateMediationTicket::route('/create'),
            'edit' => Pages\EditMediationTicket::route('/{record}/edit'),
        ];
    }
}
