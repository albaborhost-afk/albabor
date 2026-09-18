<?php

namespace App\Support;

class ListingCatalog
{
    public const BRANDS = ['Bénéteau', 'Jeanneau', 'Quicksilver', 'Bayliner', 'Sessa Marine', 'Bavaria', 'Azimut', 'Princess', 'Zodiac', 'Capelli', 'Yamaha', 'Sea-Doo', 'Kawasaki', 'Mercury', 'Suzuki', 'Honda', 'Volvo Penta'];

    public const DRIVE_TYPES = ['Ligne d’arbre', 'Embase', 'IPS 360°', 'Jet moteur'];

    public const COUNTRIES = [
        'Algérie' => '🇩🇿', 'Tunisie' => '🇹🇳', 'Maroc' => '🇲🇦', 'Égypte' => '🇪🇬',
        'Espagne' => '🇪🇸', 'France' => '🇫🇷', 'Italie' => '🇮🇹', 'Grèce' => '🇬🇷',
        'Croatie' => '🇭🇷', 'Turquie' => '🇹🇷', 'Liban' => '🇱🇧', 'Malte' => '🇲🇹',
        'Monaco' => '🇲🇨', 'Slovénie' => '🇸🇮', 'Pologne' => '🇵🇱',
    ];

    public const COUNTRY_FLAG_IMAGES = [
        'Algérie' => 'images/flags/countries/algeria.png',
        'Espagne' => 'images/flags/countries/spain.png',
        'France' => 'images/flags/countries/france.png',
    ];

    public const EQUIPMENT = [
        'equipement' => [
            'Radeau de survie', 'Alarme de niveau d’eau', 'Gilets et bouée de sauvetage',
            'Extincteurs', 'Détecteur de fumée', 'Kit de signalisation', 'Gilets de sauvetage',
            'Pompe de cale', 'Bouée de sauvetage', 'Trousse de premiers secours',
            'Pompe de cale manuelle', 'AIS', 'Balise EPIRB', 'Projecteur / Feu de recherche',
            'Caméras de surveillance', 'Alarme de bord',
        ],
        'options' => [
            'Bimini', 'Pont en teck naturel', 'Climatisation', 'Chauffage', 'Groupe électrogène',
            'Dessalinisateur', 'Eau chaude', 'Réfrigérateur', 'Congélateur', 'Four',
            'Plaque de cuisson', 'Micro-ondes', 'Lave-vaisselle', 'Lave-linge', 'WC électrique',
            'Douche', 'Douchette de pont', 'Vivier', 'TV', 'Système audio', 'Wi-Fi à bord',
            'Éclairage LED', 'Convertisseur 12V/220V', 'Prise 220V', 'Batteries de service',
        ],
        'electronique' => [
            'GPS / Traceur de cartes', 'Radio VHF', 'Sondeur', 'Pilote automatique', 'Radar',
            'Compas électronique', 'VHF', 'Guindeau électrique', 'Système de navigation intégré',
            'Propulseur d’étrave', 'Caméras de surveillance', 'Système d’alarme',
            'Traceur GPS / Géolocalisation', 'Panneaux solaires', 'Chargeur de batteries',
            'Télécommande de manœuvre',
        ],
        'extras' => [
            'Propulseur d’étrave arrière', 'Télécommande de manœuvre à distance',
            'Plateforme de bain', 'Jetski', 'Annexe', 'Moteur hors-bord pour annexe',
            'Paddle / Équipement nautique', 'Équipement de pêche', 'Barbecue extérieur',
        ],
    ];

    public const EQUIPMENT_LABELS = [
        'equipement' => 'Équipements de sécurité', 'options' => 'Équipements de confort',
        'electronique' => 'Équipements électroniques', 'extras' => 'Options / Équipements en plus',
    ];

    /**
     * Every spec leaf the website, the API and the apps may store, by section.
     *
     * Each leaf needs its own validation rule: Laravel drops unvalidated keys
     * from validated() when a rule with `array` also has nested rules, so a
     * field missing here is silently discarded on save.
     */
    public const SPEC_FIELDS = [
        'general' => [
            'modele', 'fabricant', 'annee_construction', 'immatriculation',
            'immatriculation_autre', 'nombre_places', 'part_number', 'part_type',
            'compatible_with',
        ],
        'dimensions' => [
            'longueur', 'largeur', 'tirant_eau', 'tirant_air', 'tonnage',
            'tonnage_t', 'tonnage_unit',
        ],
        'motorisation' => [
            'marque_moteur', 'propulsion', 'type_carburant', 'type_helice',
            'nombre_moteurs', 'puissance_par_moteur', 'puissance_totale',
            'nombre_heures', 'cylindree', 'nombre_cylindres', 'refroidissement',
        ],
        'reservoirs' => [
            'nombre_reservoirs', 'reservoir_carburant', 'reservoir_eau_douce', 'stockage',
        ],
        'amenagements' => [
            'nombre_cabines', 'nombre_couchettes', 'nombre_cuisine', 'nombre_sanitaire',
        ],
        'extras' => [
            'remorque', 'marque_remorque', 'place_au_port', 'adresse_port',
            'longueur_place', 'largeur_place', 'annexe',
        ],
    ];

    /**
     * Validation rules covering the whole specs payload.
     *
     * The leaves stay untyped on purpose: the website posts strings while the
     * apps post JSON numbers and booleans, and a type rule would reject one of
     * them. normalizeSpecs() trims and drops blanks instead.
     */
    public static function specRules(): array
    {
        $rules = [
            'specs' => 'nullable|array',
            'specs.tags.*' => 'nullable|array',
            'specs.tags.*.*' => 'string|max:150',
        ];

        foreach (self::SPEC_FIELDS as $section => $fields) {
            foreach ($fields as $field) {
                $rules["specs.{$section}.{$field}"] = 'nullable';
            }
        }

        return $rules;
    }

    public static function normalizeSpecs(?array $specs): ?array
    {
        if ($specs === null) {
            return null;
        }

        $number = static fn ($value): float => max(0, (float) str_replace(',', '.', (string) ($value ?? 0)));
        if (isset($specs['reservoirs']) && is_array($specs['reservoirs'])) {
            $tanks = &$specs['reservoirs'];
            $count = max(1, (int) ($tanks['nombre_reservoirs'] ?? 1));
            $total = $count * $number($tanks['reservoir_carburant'] ?? 0);
            $capacity = $total + $number($tanks['reservoir_eau_douce'] ?? 0) + $number($tanks['stockage'] ?? 0);
            // Only keep the computed totals when the seller entered something.
            // Writing zeros made the detail page render an empty Reservoirs card.
            $tanks['total_carburant'] = $total > 0 ? $total : null;
            $tanks['capacite_totale'] = $capacity > 0 ? $capacity : null;
            unset($tanks);
        }
        if (isset($specs['motorisation']) && is_array($specs['motorisation'])) {
            $motor = &$specs['motorisation'];
            if ($number($motor['nombre_moteurs'] ?? 0) > 0 && $number($motor['puissance_par_moteur'] ?? 0) > 0) {
                $motor['puissance_totale'] = (int) $motor['nombre_moteurs'] * $number($motor['puissance_par_moteur']);
            }
        }
        foreach (array_keys(self::EQUIPMENT) as $group) {
            // Older admin saves stored comma-separated strings. Read them as
            // lists too, so opening and saving the new form retains those tags.
            if (isset($specs['tags'][$group]) && is_string($specs['tags'][$group])) {
                $specs['tags'][$group] = explode(',', $specs['tags'][$group]);
            }
            if (isset($specs['tags'][$group]) && is_array($specs['tags'][$group])) {
                $specs['tags'][$group] = array_values(array_unique(array_filter(array_map('trim', $specs['tags'][$group]))));
            }
        }

        // Drop blank leaves so a section is only considered present when it has
        // something to show, and keep stored strings tidy.
        foreach ($specs as $section => $fields) {
            if ($section === 'tags' || !is_array($fields)) {
                continue;
            }
            foreach ($fields as $key => $value) {
                // No truncation here: this also runs on retrieved(), so
                // shortening a stored value would quietly persist the cut.
                if (is_string($value)) {
                    $value = trim($value);
                    $specs[$section][$key] = $value;
                }
                if ($value === null || $value === '' || $value === []) {
                    unset($specs[$section][$key]);
                }
            }
            if ($specs[$section] === []) {
                unset($specs[$section]);
            }
        }

        return $specs;
    }
}
