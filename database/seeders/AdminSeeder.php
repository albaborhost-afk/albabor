<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
