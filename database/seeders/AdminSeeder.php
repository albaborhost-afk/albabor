<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Remove old admin if email changed
        User::where('email', 'admin@dzboats.dz')
            ->where('account_type', 'admin')
            ->delete();

        User::updateOrCreate(
            ['email' => 'Albabordz@gmail.com'],
            [
                'name' => 'Admin Albabor',
                'phone' => '0550000000',
                'password' => Hash::make('BILALbilal16@'),
                'account_type' => 'admin',
                'verified_badge' => true,
                'verification_status' => 'approved',
            ]
        );
    }
}
