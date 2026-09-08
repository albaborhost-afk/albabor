<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListingSearch
{
    public static function apply(Builder $query, Request $request, bool $legacyWebCountry = false): Builder
    {
        foreach (['category', 'etat', 'type_offre', 'currency'] as $key) {
            if ($request->filled($key)) {
                $query->where($key, $request->input($key));
            }
        }
        if ($request->filled('type')) {
            $allowed = \App\Models\Listing::CATEGORY_TYPES[$request->input('category')] ?? null;
            if ($allowed === null || isset($allowed[$request->input('type')])) {
                $query->where('type', $request->input('type'));
            }
        }
        if ($request->filled('pays')) {
            $query->where('pays', $request->input('pays'));
        }
        if ($request->filled('wilaya')) {
            $query->where($legacyWebCountry && ! $request->filled('pays') ? 'pays' : 'wilaya', $request->input('wilaya'));
        }
        if ($request->filled('location')) {
            $term = '%'.$request->input('location').'%';
            $query->where(fn ($q) => $q->where('wilaya', 'like', $term)->orWhere('visible_a', 'like', $term));
        }
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }
        foreach (['min' => '>=', 'max' => '<='] as $bound => $operator) {
            if ($request->filled('price_'.$bound) && is_numeric($request->input('price_'.$bound))) {
                $query->where('price_dzd', $operator, (float) $request->input('price_'.$bound));
            }
        }
        foreach (['fabricant' => 'general->fabricant', 'engine_brand' => 'motorisation->marque_moteur'] as $key => $path) {
            if ($request->filled($key)) {
                $query->where('specs->'.$path, 'like', '%'.$request->input($key).'%');
            }
        }
        foreach (['propulsion' => 'propulsion', 'fuel_type' => 'type_carburant', 'drive_type' => 'type_helice'] as $key => $field) {
            if ($request->filled($key)) {
                $query->where('specs->motorisation->'.$field, $request->input($key));
            }
        }
        $numeric = [
            'year' => 'general->annee_construction', 'length' => 'dimensions->longueur',
            'width' => 'dimensions->largeur', 'power' => 'motorisation->puissance_totale',
            'cabins' => 'amenagements->nombre_cabines', 'berths' => 'amenagements->nombre_couchettes',
        ];
        foreach ($numeric as $key => $path) {
            foreach (['min' => '>=', 'max' => '<='] as $bound => $operator) {
                self::number($query, $request, $key.'_'.$bound, $path, $operator);
            }
        }
        self::number($query, $request, 'engine_count', 'motorisation->nombre_moteurs', '=');

        return $query;
    }

    private static function number(Builder $query, Request $request, string $key, string $path, string $operator): void
    {
        if (! $request->filled($key) || ! is_numeric($request->input($key))) {
            return;
        }
        // Cast both JSON numbers and numeric strings; let Laravel quote JSON paths for each database.
        $column = $query->getQuery()->getGrammar()->wrap('specs->'.$path);
        $query->whereRaw("CAST({$column} AS DECIMAL(16, 3)) {$operator} ?", [(float) $request->input($key)]);
    }
}
