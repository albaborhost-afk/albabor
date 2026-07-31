<?php

namespace App\Filament\Resources\BannerRequestResource\Pages;

use App\Filament\Resources\BannerRequestResource;
use App\Models\BannerRequest;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBannerRequests extends ListRecords
{
    protected static string $resource = BannerRequestResource::class;

    /** « Nouvelles » en premier : c'est la seule liste qui demande une action. */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('Nouvelles')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BannerRequest::STATUS_NEW))
                ->badge(BannerRequest::where('status', BannerRequest::STATUS_NEW)->count())
                ->badgeColor('warning'),

            'contacted' => Tab::make('Contactées')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BannerRequest::STATUS_CONTACTED)),

            'accepted' => Tab::make('Acceptées')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BannerRequest::STATUS_ACCEPTED)),

            'all' => Tab::make('Toutes'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'new';
    }
}
