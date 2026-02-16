<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'vendeur@dzboats.dz'],
            [
                'name' => 'Vendeur Test',
                'phone' => '0555123456',
                'password' => bcrypt('password'),
                'account_type' => 'user',
                'verified_badge' => false,
                'verification_status' => 'none',
            ]
        );

        $listings = [
            [
                'title' => 'Bateau de pêche 6m - Excellent état',
                'description' => 'Bateau de pêche en fibre de verre, 6 mètres de longueur. Moteur Yamaha 40CV inclus. Parfait pour la pêche côtière. Équipé de GPS et sondeur. Visible à Alger.',
                'category' => 'boat',
                'price_dzd' => 850000,
                'currency' => 'DZD',
                'type_offre' => 'negociable',
                'etat' => 'bon_etat',
                'remarque_echange' => 'accepte',
                'mediation_enabled' => false,
                'visible_a' => 'all',
                'wilaya' => '16',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 45,
                'favorites_count' => 8,
            ],
            [
                'title' => 'Jet-ski Yamaha VX 2022',
                'description' => 'Jet-ski Yamaha VX en excellent état. Année 2022, faible kilométrage. Idéal pour les loisirs nautiques. Remorque incluse.',
                'category' => 'jetski',
                'price_dzd' => 1200000,
                'currency' => 'DZD',
                'type_offre' => 'fix',
                'etat' => 'comme_neuf',
                'remarque_echange' => 'refuse',
                'mediation_enabled' => true,
                'visible_a' => 'all',
                'wilaya' => '31',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'featured_until' => now()->addDays(15),
                'views_count' => 120,
                'favorites_count' => 25,
            ],
            [
                'title' => 'Bateau semi-rigide 4.5m neuf',
                'description' => 'Semi-rigide neuf de 4.5 mètres. Structure en fibre de verre avec boudins en hypalon. Capacité 6 personnes. Moteur non inclus.',
                'category' => 'boat',
                'price_dzd' => 450000,
                'currency' => 'DZD',
                'type_offre' => 'negociable',
                'etat' => 'jamais_utilise',
                'remarque_echange' => 'refuse',
                'mediation_enabled' => false,
                'visible_a' => 'all',
                'wilaya' => '23',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 78,
                'favorites_count' => 12,
            ],
            [
                'title' => 'Moteur hors-bord Mercury 60CV',
                'description' => 'Moteur Mercury 60CV 4 temps. Année 2020, bien entretenu. Heures de fonctionnement: 150h. Parfait état de marche.',
                'category' => 'engine',
                'price_dzd' => 380000,
                'currency' => 'DZD',
                'type_offre' => 'negociable',
                'etat' => 'bon_etat',
                'remarque_echange' => 'accepte',
                'mediation_enabled' => true,
                'visible_a' => 'all',
                'wilaya' => '06',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 35,
                'favorites_count' => 5,
            ],
            [
                'title' => 'Hélice inox pour Yamaha 40-60CV',
                'description' => 'Hélice en acier inoxydable pour moteurs Yamaha 40-60CV. 3 pales, 13 pouces. Neuve dans son emballage.',
                'category' => 'parts',
                'price_dzd' => 25000,
                'currency' => 'DZD',
                'type_offre' => 'fix',
                'etat' => 'jamais_utilise',
                'remarque_echange' => 'refuse',
                'mediation_enabled' => false,
                'visible_a' => 'all',
                'wilaya' => '16',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 22,
                'favorites_count' => 3,
            ],
            [
                'title' => 'Yacht de luxe 12m - Occasion exceptionnelle',
                'description' => 'Magnifique yacht de 12 mètres. 2 cabines, cuisine équipée, douche. Moteurs diesel récemment révisés. Documents en règle.',
                'category' => 'boat',
                'price_dzd' => 8500000,
                'currency' => 'DZD',
                'type_offre' => 'negociable',
                'etat' => 'bon_etat',
                'remarque_echange' => 'accepte',
                'mediation_enabled' => true,
                'visible_a' => 'all',
                'wilaya' => '31',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'featured_until' => now()->addDays(20),
                'views_count' => 250,
                'favorites_count' => 45,
            ],
            [
                'title' => 'Sea-Doo Spark 90CV 2023',
                'description' => 'Sea-Doo Spark 2 places, 90CV. Comme neuf, utilisé seulement 2 fois. Garantie constructeur encore valide.',
                'category' => 'jetski',
                'price_dzd' => 950000,
                'currency' => 'DZD',
                'type_offre' => 'fix',
                'etat' => 'comme_neuf',
                'remarque_echange' => 'refuse',
                'mediation_enabled' => false,
                'visible_a' => 'all',
                'wilaya' => '42',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 88,
                'favorites_count' => 18,
            ],
            [
                'title' => 'Bateau de pêche artisanal 5m',
                'description' => 'Barque de pêche traditionnelle en bois, 5 mètres. Robuste et stable. Idéal pour la pêche en mer ou en lac.',
                'category' => 'boat',
                'price_dzd' => 180000,
                'currency' => 'DZD',
                'type_offre' => 'negociable',
                'etat' => 'etat_moyen',
                'remarque_echange' => 'accepte',
                'mediation_enabled' => false,
                'visible_a' => 'all',
                'wilaya' => '21',
                'status' => 'active',
                'published_until' => now()->addDays(365),
                'views_count' => 56,
                'favorites_count' => 7,
            ],
        ];

        foreach ($listings as $listingData) {
            Listing::updateOrCreate(
                ['title' => $listingData['title'], 'user_id' => $user->id],
                array_merge($listingData, ['user_id' => $user->id])
            );
        }
    }
}
