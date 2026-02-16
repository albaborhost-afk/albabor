<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
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
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
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
                            ->default('user'),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
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
                Tables\Filters\TernaryFilter::make('verified_badge')
                    ->label('Badge vérifié'),
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Bloqué'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleBlock')
                    ->label(fn (User $record): string => $record->is_blocked ? 'Débloquer' : 'Bloquer')
                    ->icon(fn (User $record): string => $record->is_blocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (User $record): string => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_blocked' => !$record->is_blocked])),
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
