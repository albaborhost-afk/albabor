<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Mensuel',
                'description' => 'Abonnement mensuel pour vendeurs professionnels. Publiez des moteurs et pièces détachées.',
                'price_dzd' => 3000,
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Trimestriel',
                'description' => 'Abonnement 3 mois pour vendeurs professionnels. Économisez 10%.',
                'price_dzd' => 8100,
                'duration_days' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Annuel',
                'description' => 'Abonnement annuel pour vendeurs professionnels. Économisez 25%.',
                'price_dzd' => 27000,
                'duration_days' => 365,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
