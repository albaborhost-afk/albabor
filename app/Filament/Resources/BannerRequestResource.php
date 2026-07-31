<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerRequestResource\Pages;
use App\Models\BannerRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Demandes d'espace publicitaire reçues depuis le site.
 *
 * L'écran est fait pour une seule chose : rappeler l'annonceur vite. D'où les
 * boutons WhatsApp et e-mail directement dans la liste, et un statut pour
 * savoir qui a déjà été traité.
 */
class BannerRequestResource extends Resource
{
    protected static ?string $model = BannerRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Demandes de publicité';

    protected static ?string $modelLabel = 'Demande de publicité';

    protected static ?string $pluralModelLabel = 'Demandes de publicité';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 6;

    /** Pastille dans le menu : les demandes non traitées doivent sauter aux yeux. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', BannerRequest::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Demande de l\'annonceur')
                ->description('Envoyé depuis le formulaire public — ces champs ne sont pas modifiables.')
                ->schema([
                    Forms\Components\TextInput::make('contact_name')
                        ->label('Nom du contact')
                        ->disabled(),
                    Forms\Components\TextInput::make('company_name')
                        ->label('Entreprise')
                        ->placeholder('—')
                        ->disabled(),
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->disabled(),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label('WhatsApp')
                        ->formatStateUsing(fn (?BannerRequest $record): string => $record?->full_whatsapp ?? '')
                        ->disabled(),
                    Forms\Components\Textarea::make('message')
                        ->label('Ce qu\'il souhaite annoncer')
                        ->rows(6)
                        ->columnSpanFull()
                        ->disabled(),
                    Forms\Components\TextInput::make('budget_dzd')
                        ->label('Budget envisagé')
                        ->suffix('DA')
                        ->placeholder('Non précisé')
                        ->disabled(),
                    Forms\Components\Placeholder::make('created_at')
                        ->label('Reçue le')
                        ->content(fn (?BannerRequest $record): string => $record?->created_at?->translatedFormat('d F Y à H:i') ?? '—'),
                ])->columns(2),

            Forms\Components\Section::make('Suivi')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options(BannerRequest::STATUS_LABELS)
                        ->required()
                        ->native(false)
                        ->live()
                        // Passer en « Contacté » horodate automatiquement : sinon
                        // personne ne pense à remplir la date à la main.
                        ->afterStateUpdated(function (?string $state, Forms\Set $set, ?BannerRequest $record) {
                            if ($state !== BannerRequest::STATUS_NEW && ! $record?->contacted_at) {
                                $set('contacted_at', now());
                            }
                        }),
                    Forms\Components\DateTimePicker::make('contacted_at')
                        ->label('Contacté le')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Notes internes')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Visible uniquement par l\'administration.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (BannerRequest $record): string => $record->created_at->diffForHumans()),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (BannerRequest $record): ?string => $record->company_name),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(fn (BannerRequest $record): string => $record->full_whatsapp)
                    ->copyable()
                    ->copyableState(fn (BannerRequest $record): string => $record->full_whatsapp)
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Demande')
                    ->limit(60)
                    ->tooltip(fn (BannerRequest $record): string => $record->message)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('budget_dzd')
                    ->label('Budget')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state, 0, ',', ' ') . ' DA' : '—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => BannerRequest::STATUS_NEW,
                        'info'    => BannerRequest::STATUS_CONTACTED,
                        'success' => BannerRequest::STATUS_ACCEPTED,
                        'danger'  => BannerRequest::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (BannerRequest $record): string => $record->status_label),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(BannerRequest::STATUS_LABELS),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (BannerRequest $record): string => $record->whatsapp_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('email')
                    ->label('E-mail')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->url(fn (BannerRequest $record): string => 'mailto:' . $record->email),

                Tables\Actions\Action::make('markContacted')
                    ->label('Marquer contacté')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (BannerRequest $record): bool => $record->status === BannerRequest::STATUS_NEW)
                    ->action(function (BannerRequest $record): void {
                        $record->update([
                            'status'       => BannerRequest::STATUS_CONTACTED,
                            'contacted_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Demande marquée comme contactée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('Ouvrir'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune demande de publicité')
            ->emptyStateDescription('Les demandes envoyées depuis la page « Annoncez sur AlBabor » apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-megaphone');
    }

    /** Création réservée au formulaire public : rien à saisir ici. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannerRequests::route('/'),
            'edit'  => Pages\EditBannerRequest::route('/{record}/edit'),
        ];
    }
}
