<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\PhoneCountry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Utilisateurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Adresse e-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('phone_country_code')
                            ->label('Pays')
                            ->options(PhoneCountry::options())
                            ->searchable()
                            ->default('+213')
                            ->placeholder('Sélectionner un pays'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone (numéro local)')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->helperText('Numéro local sans l\'indicatif. Ex: 0676085441 pour DZ, 612345678 pour FR.'),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres du compte')
                    ->schema([
                        Forms\Components\Select::make('account_type')
                            ->label('Type de compte')
                            ->options([
                                'user' => 'Utilisateur',
                                'vendor' => 'Vendeur professionnel',
                                'admin' => 'Administrateur',
                            ])
                            ->required()
                            ->default('user')
                            ->helperText('Vendeur professionnel : peut publier toutes catégories (bateaux, jet-skis, moteurs, pièces) sans abonnement.'),
                        Forms\Components\Select::make('verification_status')
                            ->label('Statut de vérification')
                            ->options([
                                'none' => 'Non soumis',
                                'pending' => 'En attente',
                                'approved' => 'Approuvé',
                                'rejected' => 'Refusé',
                            ])
                            ->required()
                            ->default('none'),
                        Forms\Components\Toggle::make('verified_badge')
                            ->label('Badge vérifié')
                            ->helperText('Affiché sur le profil et les annonces'),
                        Forms\Components\Toggle::make('is_blocked')
                            ->label('Compte bloqué')
                            ->helperText('Empêche l\'utilisateur de se connecter'),
                        Forms\Components\Toggle::make('free_publishing')
                            ->label('Publication gratuite')
                            ->helperText('Permet de publier des annonces sans paiement — toutes catégories, illimité')
                            ->onColor('success'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_country_code')
                    ->label('Pays')
                    ->formatStateUsing(fn (?string $state): string => PhoneCountry::label($state) ?: '—')
                    ->tooltip(fn (User $record): ?string => PhoneCountry::info($record->phone_country_code)['name'] ?? null)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->formatStateUsing(function (?string $state, User $record): string {
                        if (! $state) return '—';
                        $code = $record->phone_country_code;
                        return $code ? trim($code.' '.$state) : $state;
                    })
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (User $record): string => trim(($record->phone_country_code ?? '').($record->phone ?? ''))),
                Tables\Columns\BadgeColumn::make('account_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'user',
                        'success' => 'vendor',
                        'danger' => 'admin',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user' => 'Utilisateur',
                        'vendor' => 'Vendeur',
                        'admin' => 'Admin',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('verified_badge')
                    ->label('Vérifié')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle'),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Bloqué')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\IconColumn::make('free_publishing')
                    ->label('Gratuit')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->label('Type de compte')
                    ->options([
                        'user' => 'Utilisateur',
                        'vendor' => 'Vendeur',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\SelectFilter::make('phone_country_code')
                    ->label('Pays (indicatif)')
                    ->options(PhoneCountry::options())
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('verified_badge')
                    ->label('Badge vérifié'),
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Bloqué'),
                Tables\Filters\TernaryFilter::make('free_publishing')
                    ->label('Publication gratuite'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleBlock')
                    ->label(fn (User $record): string => $record->is_blocked ? 'Débloquer' : 'Bloquer')
                    ->icon(fn (User $record): string => $record->is_blocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (User $record): string => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_blocked' => !$record->is_blocked])),
                Tables\Actions\Action::make('toggleFreePublishing')
                    ->label(fn (User $record): string => $record->free_publishing ? 'Retirer accès gratuit' : 'Accorder accès gratuit')
                    ->icon('heroicon-o-gift')
                    ->color(fn (User $record): string => $record->free_publishing ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['free_publishing' => !$record->free_publishing])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
