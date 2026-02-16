<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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

        // Run other seeders
        $this->call([
            PlanSeeder::class,
            ListingSeeder::class,
        ]);
    }
}
