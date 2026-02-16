<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Paiements';
    protected static ?string $modelLabel = 'Paiement';
    protected static ?string $pluralModelLabel = 'Paiements';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Utilisateur')
                            ->disabled(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'publish_listing' => 'Publication d\'annonce',
                                'featured_listing' => 'Mise en avant',
                                'vendor_subscription' => 'Abonnement vendeur',
                                'mediation_fee' => "Frais de médiation",
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('amount_dzd')
                            ->label('Montant (DZD)')
                            ->disabled(),
                        Forms\Components\Select::make('method')
                            ->label("Méthode")
                            ->options([
                                'baridimob' => 'BaridiMob',
                                'ccp' => 'CCP',
                                'bank_transfer' => 'Virement bancaire',
                            ])
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Justificatif')
                    ->schema([
                        Forms\Components\FileUpload::make('proof_path')
                            ->label('Justificatif')
                            ->image()
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => "Approuvé",
                                'rejected' => "Refusé",
                            ]),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du refus')
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Justificatif de paiement')
                    ->schema([
                        Infolists\Components\ImageEntry::make('proof_path')
                            ->label('')
                            ->disk('public')
                            ->height(400)
                            ->extraImgAttributes(['class' => 'rounded-lg shadow-lg'])
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Informations du paiement')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),
                        Infolists\Components\TextEntry::make('amount_dzd')
                            ->label('Montant')
                            ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' DA'),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'publish_listing' => 'primary',
                                'featured_listing' => 'warning',
                                'vendor_subscription' => 'success',
                                'mediation_fee' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'publish_listing' => 'Publication d\'annonce',
                                'featured_listing' => 'Mise en avant',
                                'vendor_subscription' => 'Abonnement vendeur',
                                'mediation_fee' => "Frais de médiation",
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('method')
                            ->label("Méthode")
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'baridimob' => 'success',
                                'ccp' => 'info',
                                'bank_transfer' => 'purple',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'baridimob' => 'BaridiMob',
                                'ccp' => 'CCP',
                                'bank_transfer' => 'Virement bancaire',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'En attente',
                                'approved' => "Approuvé",
                                'rejected' => "Refusé",
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label("Date de création")
                            ->dateTime("d/m/Y à H:i"),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Raison du refus')
                            ->visible(fn ($record) => $record->status === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Utilisateur')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Nom')
                            ->url(fn ($record) => $record->user ? UserResource::getUrl('edit', ['record' => $record->user]) : null),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label("Téléphone")
                            ->copyable(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make("Annonce liée")
                    ->schema([
                        Infolists\Components\TextEntry::make('listing.title')
                            ->label('Titre')
                            ->url(fn ($record) => $record->listing ? ListingResource::getUrl('edit', ['record' => $record->listing]) : null),
                        Infolists\Components\TextEntry::make('listing.category')
                            ->label("Catégorie")
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'boat' => 'Bateau',
                                'jetski' => 'Jet-ski',
                                'engine' => 'Moteur',
                                'parts' => "Pièces",
                                default => $state ?? '-',
                            }),
                        Infolists\Components\TextEntry::make('listing.price_dzd')
                            ->label('Prix')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', ' ') . ' DA' : '-'),
                        Infolists\Components\TextEntry::make('listing.status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn ($state): string => match ($state) {
                                'active' => 'success',
                                'pending_review' => 'warning',
                                'awaiting_payment' => 'info',
                                'rejected' => 'danger',
                                'sold' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => $record->listing_id !== null),

                Infolists\Components\Section::make('Approbation')
                    ->schema([
                        Infolists\Components\TextEntry::make('approvedBy.name')
                            ->label("Approuvé par"),
                        Infolists\Components\TextEntry::make('approved_at')
                            ->label('Date d\'approbation')
                            ->dateTime("d/m/Y à H:i"),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->status === 'approved'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ImageColumn::make('proof_path')
                    ->label('Justificatif')
                    ->disk('public')
                    ->square()
                    ->size(50),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? UserResource::getUrl('edit', ['record' => $record->user]) : null)
                    ->color('primary')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publish_listing' => 'primary',
                        'featured_listing' => 'warning',
                        'vendor_subscription' => 'success',
                        'mediation_fee' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'publish_listing' => 'Publication',
                        'featured_listing' => 'Mise en avant',
                        'vendor_subscription' => 'Abonnement',
                        'mediation_fee' => "Médiation",
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount_dzd')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' DA')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('method')
                    ->label("Méthode")
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baridimob' => 'success',
                        'ccp' => 'info',
                        'bank_transfer' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baridimob' => 'BaridiMob',
                        'ccp' => 'CCP',
                        'bank_transfer' => 'Virement',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => "Approuvé",
                        'rejected' => "Refusé",
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Annonce')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->listing?->title)
                    ->toggleable()
                    ->url(fn ($record) => $record->listing ? ListingResource::getUrl('edit', ['record' => $record->listing]) : null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->description(fn ($record) => $record->created_at->format('H:i'))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => "Approuvé",
                        'rejected' => "Refusé",
                    ])
                    ->placeholder('Tous les statuts'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'publish_listing' => 'Publication',
                        'featured_listing' => 'Mise en avant',
                        'vendor_subscription' => 'Abonnement',
                        'mediation_fee' => "Médiation",
                    ])
                    ->placeholder('Tous les types'),

                Tables\Filters\SelectFilter::make('method')
                    ->label("Méthode")
                    ->options([
                        'baridimob' => 'BaridiMob',
                        'ccp' => 'CCP',
                        'bank_transfer' => 'Virement bancaire',
                    ])
                    ->placeholder("Toutes les méthodes"),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Du ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Au ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\Action::make('viewProof')
                    ->label('Voir')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->modalHeading('Justificatif de paiement')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(fn (Payment $record) => view('filament.modals.payment-proof', [
                        'proofUrl' => Storage::url($record->proof_path),
                    ])),

                Tables\Actions\ViewAction::make()
                    ->label("Détails")
                    ->icon('heroicon-o-eye'),

                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approuver le paiement')
                    ->modalDescription("Êtes-vous sûr de vouloir approuver ce paiement ? Cette action est irréversible.")
                    ->modalSubmitActionLabel('Oui, approuver')
                    ->action(function (Payment $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);

                        // Handle listing publish payment
                        if ($record->type === 'publish_listing' && $record->listing) {
                            $updateData = ['published_until' => now()->addYear()];
                            if ($record->listing->status === 'awaiting_payment') {
                                $updateData['status'] = 'pending_review';
                            }
                            $record->listing->update($updateData);
                        }

                        // Handle featured payment
                        if ($record->type === 'featured_listing' && $record->listing) {
                            $record->listing->update(['featured_until' => now()->addDays(30)]);
                        }

                        // Handle subscription payment
                        if ($record->type === 'vendor_subscription' && $record->subscription) {
                            $record->subscription->update([
                                'status' => 'active',
                                'starts_at' => now(),
                                'ends_at' => now()->addDays($record->subscription->plan->duration_days ?? 30),
                            ]);
                        }

                        // Handle mediation fee payment
                        if ($record->type === 'mediation_fee' && $record->mediationTicket) {
                            $record->mediationTicket->update(['payment_status' => 'paid']);
                        }

                        Notification::make()
                            ->title("Paiement approuvé")
                            ->body("Le paiement a été approuvé avec succès.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->modalHeading('Refuser le paiement')
                    ->modalDescription('Veuillez indiquer la raison du refus.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du refus')
                            ->placeholder('Ex: Justificatif illisible, montant incorrect...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title("Paiement refusé")
                            ->body("Le paiement a été refusé.")
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
