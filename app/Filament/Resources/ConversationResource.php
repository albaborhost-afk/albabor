<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Support';

    protected static ?string $modelLabel = 'Conversation';

    protected static ?string $pluralModelLabel = 'Conversations';

    public static function canCreate(): bool
    {
        return false;
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
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Dernier message')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Details de la conversation')
                    ->schema([
                        Infolists\Components\TextEntry::make('listing.title')
                            ->label('Annonce'),
                        Infolists\Components\TextEntry::make('buyer.name')
                            ->label('Acheteur'),
                        Infolists\Components\TextEntry::make('seller.name')
                            ->label('Vendeur'),
                        Infolists\Components\TextEntry::make('messages_count')
                            ->label('Nombre de messages')
                            ->state(fn (Conversation $record) => $record->messages()->count()),
                        Infolists\Components\TextEntry::make('last_message_at')
                            ->label('Dernier message')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Cree le')
                            ->dateTime('d/m/Y H:i'),
                    ])->columns(3),
                Infolists\Components\Section::make('Messages')
                    ->schema([
                        Infolists\Components\ViewEntry::make('messages')
                            ->view('filament.resources.conversation-resource.messages-list'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }
}
