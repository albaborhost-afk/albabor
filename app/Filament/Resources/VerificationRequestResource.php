<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationRequestResource\Pages;
use App\Filament\Resources\VerificationRequestResource\RelationManagers;
use App\Models\VerificationRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VerificationRequestResource extends Resource
{
    protected static ?string $model = VerificationRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Utilisateurs';

    protected static ?string $modelLabel = 'Demande de vérification';

    protected static ?string $pluralModelLabel = 'Demandes de vérification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Approuvé',
                                'rejected' => 'Rejeté',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'rejected'),
                    ]),
                Forms\Components\Section::make('Document')
                    ->schema([
                        Forms\Components\FileUpload::make('document_path')
                            ->label('Document d\'identité')
                            ->directory('verification-documents')
                            ->image()
                            ->imagePreviewHeight('300')
                            ->openable()
                            ->downloadable()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Examen')
                    ->schema([
                        Forms\Components\Select::make('reviewed_by')
                            ->relationship('reviewer', 'name')
                            ->label('Examiné par')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Date d\'examen')
                            ->disabled(),
                    ])
                    ->visible(fn (?VerificationRequest $record): bool => $record !== null && $record->reviewed_at !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('document_path')
                    ->label('Document')
                    ->disk('public')
                    ->circular(false)
                    ->height(40)
                    ->width(60),
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
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Examiné par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Date d\'examen')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approuver la vérification')
                    ->modalDescription('Voulez-vous vraiment approuver cette demande de vérification ? L\'utilisateur recevra le badge vérifié.')
                    ->modalSubmitActionLabel('Oui, approuver')
                    ->visible(fn (VerificationRequest $record): bool => $record->status === 'pending')
                    ->action(function (VerificationRequest $record): void {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'rejection_reason' => null,
                        ]);

                        $record->user->update([
                            'verified_badge' => true,
                            'verification_status' => 'approved',
                        ]);

                        Notification::make()
                            ->title('Demande approuvée')
                            ->body('L\'utilisateur ' . $record->user->name . ' a reçu le badge vérifié.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter la vérification')
                    ->modalDescription('Veuillez indiquer la raison du rejet.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn (VerificationRequest $record): bool => $record->status === 'pending')
                    ->action(function (VerificationRequest $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->user->update([
                            'verified_badge' => false,
                            'verification_status' => 'rejected',
                        ]);

                        Notification::make()
                            ->title('Demande rejetée')
                            ->body('La demande de ' . $record->user->name . ' a été rejetée.')
                            ->danger()
                            ->send();
                    }),
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
            'index' => Pages\ListVerificationRequests::route('/'),
            'create' => Pages\CreateVerificationRequest::route('/create'),
            'edit' => Pages\EditVerificationRequest::route('/{record}/edit'),
        ];
    }
}
