<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Plans are real production data.
        $this->call([
            PlanSeeder::class,
        ]);

        // The accounts below share one well-known password and exist only to
        // develop against. Creating them in production would hand out an admin
        // login, so they are never seeded outside local/testing.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Skipped demo accounts: they are only seeded in local/testing.');

            return;
        }

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@dzboats.dz'],
            [
                'name' => 'Admin DZ Boats',
                'phone' => '0550000000',
                'password' => Hash::make('password'),
                'account_type' => 'admin',
                'verified_badge' => true,
                'verification_status' => 'approved',
            ]
        );

        // Create test vendor user
        User::updateOrCreate(
            ['email' => 'vendeur@dzboats.dz'],
            [
                'name' => 'Vendeur Pro',
                'phone' => '0555123456',
                'password' => Hash::make('password'),
                'account_type' => 'vendor',
                'verified_badge' => true,
                'verification_status' => 'approved',
            ]
        );

        // Create test regular user
        User::updateOrCreate(
            ['email' => 'user@dzboats.dz'],
            [
                'name' => 'Utilisateur Test',
                'phone' => '0555789012',
                'password' => Hash::make('password'),
                'account_type' => 'user',
                'verified_badge' => false,
                'verification_status' => 'none',
            ]
        );
    }
}
