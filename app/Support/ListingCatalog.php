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

    public static function normalizeSpecs(?array $specs): ?array
    {
        if ($specs === null) {
            return null;
        }

        $number = static fn ($value): float => max(0, (float) str_replace(',', '.', (string) ($value ?? 0)));
        if (isset($specs['reservoirs']) && is_array($specs['reservoirs'])) {
            $tanks = &$specs['reservoirs'];
            $count = max(1, (int) ($tanks['nombre_reservoirs'] ?? 1));
            $tanks['total_carburant'] = $count * $number($tanks['reservoir_carburant'] ?? 0);
            $tanks['capacite_totale'] = $tanks['total_carburant']
                + $number($tanks['reservoir_eau_douce'] ?? 0) + $number($tanks['stockage'] ?? 0);
        }
        if (isset($specs['motorisation']) && is_array($specs['motorisation'])) {
            $motor = &$specs['motorisation'];
            if ($number($motor['nombre_moteurs'] ?? 0) > 0 && $number($motor['puissance_par_moteur'] ?? 0) > 0) {
                $motor['puissance_totale'] = (int) $motor['nombre_moteurs'] * $number($motor['puissance_par_moteur']);
            }
        }
        foreach (array_keys(self::EQUIPMENT) as $group) {
            if (isset($specs['tags'][$group]) && is_array($specs['tags'][$group])) {
                $specs['tags'][$group] = array_values(array_unique(array_filter(array_map('trim', $specs['tags'][$group]))));
            }
        }

        return $specs;
    }
}
