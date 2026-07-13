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

        $email    = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // Never hardcode credentials here: this file is committed and the repo
        // history is permanent.
        if (! $email || ! $password) {
            $this->command?->warn('AdminSeeder skipped: set ADMIN_EMAIL and ADMIN_PASSWORD to provision the admin account.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Albabor',
                'phone' => '0550000000',
                'password' => Hash::make($password),
                'account_type' => 'admin',
                'verified_badge' => true,
                'verification_status' => 'approved',
            ]
        );
    }
}
