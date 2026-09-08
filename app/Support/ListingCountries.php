<?php

namespace App\Support;

use App\Models\Listing;
use Illuminate\Support\Collection;

class ListingCountries
{
    public static function groups(): Collection
    {
        return Listing::active()->whereNotNull('pays')->where('pays', '!=', '')
            ->selectRaw('pays, COUNT(*) AS listing_count')->groupBy('pays')
            ->orderByRaw('CASE WHEN pays = ? THEN 0 ELSE 1 END', ['Algérie'])->orderBy('pays')
            ->get()->map(fn ($country) => [
                'country' => $country->pays,
                'flag' => ListingCatalog::COUNTRIES[$country->pays] ?? '🌍',
                'count' => (int) $country->listing_count,
                'listings' => Listing::active()->with(['user', 'media'])->where('pays', $country->pays)
                    ->orderByRaw('COALESCE(last_renewed_at, created_at) DESC')->limit(6)->get(),
            ]);
    }
}
