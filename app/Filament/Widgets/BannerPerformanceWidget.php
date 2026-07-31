<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Rendement des panneaux publicitaires.
 *
 * C'est ce qu'on montre à l'annonceur : combien de fois sa bannière a été
 * diffusée, combien de clics elle a rapportés, et le taux de clic — le seul
 * chiffre qui permette de comparer deux bannières entre elles.
 */
class BannerPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Panneaux publicitaires')
            ->description('Diffusions, clics et taux de clic — site et application confondus')
            ->headerActions([
                Tables\Actions\Action::make('allBanners')
                    ->label('Gérer les bannières')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.banners.index'))
                    ->color('gray')
                    ->size('sm'),
            ])
            ->query(Banner::query())
            ->defaultSort('view_count', 'desc')
            ->paginated([10, 25])
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk(config('filesystems.listing_disk', 'public'))
                    ->height(38),

                Tables\Columns\TextColumn::make('title')
                    ->label('Bannière')
                    ->searchable()
                    ->limit(35)
                    ->weight('medium')
                    ->color('primary')
                    ->url(fn (Banner $record) => BannerResource::getUrl('edit', ['record' => $record])),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Annonceur')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('En ligne')
                    ->boolean()
                    ->trueIcon('heroicon-o-play-circle')
                    ->falseIcon('heroicon-o-pause-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Diffusions')
                    ->alignCenter()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state, 0, ',', ' ')),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clics')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state, 0, ',', ' ')),

                Tables\Columns\TextColumn::make('click_through_rate')
                    ->label('Taux de clic')
                    ->alignCenter()
                    ->badge()
                    // Repère du secteur : ~1 % est correct pour une bannière display.
                    ->color(fn (Banner $record): string => match (true) {
                        $record->view_count === 0 => 'gray',
                        $record->click_through_rate >= 1.0 => 'success',
                        $record->click_through_rate >= 0.3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (Banner $record): string => $record->view_count === 0
                        ? '—'
                        : $record->click_through_rate . ' %')
                    ->tooltip('Clics ÷ diffusions. Un panneau display se situe généralement autour de 1 %.'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin de diffusion')
                    ->dateTime('d/m/Y')
                    ->placeholder('Sans limite')
                    ->color(fn (Banner $record): string => $record->ends_at && $record->ends_at->isPast() ? 'danger' : 'gray')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('En ligne')
                    ->placeholder('Toutes')
                    ->trueLabel('En ligne')
                    ->falseLabel('Hors ligne'),
            ])
            ->emptyStateHeading('Aucun panneau publicitaire')
            ->emptyStateDescription('Créez une bannière pour commencer à mesurer ses diffusions et ses clics.')
            ->emptyStateIcon('heroicon-o-megaphone');
    }
}
