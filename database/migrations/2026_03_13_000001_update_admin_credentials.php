<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Remove old admin
        DB::table('users')
            ->where('email', 'admin@dzboats.dz')
            ->where('account_type', 'admin')
            ->delete();

        $email    = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // Never hardcode credentials here: this file is committed and the repo
        // history is permanent. Without both env vars set, provision the admin
        // out-of-band instead (`php artisan user:make-admin {email}`).
        if (! $email || ! $password) {
            return;
        }

        $exists = DB::table('users')->where('email', $email)->exists();

        if ($exists) {
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'password'            => Hash::make($password),
                    'account_type'        => 'admin',
                    'verified_badge'      => true,
                    'verification_status' => 'approved',
                    'is_blocked'          => false,
                    'updated_at'          => now(),
                ]);
        } else {
            DB::table('users')->insert([
                'name'                => 'Admin Albabor',
                'email'               => $email,
                'phone'               => '0550000000',
                'password'            => Hash::make($password),
                'account_type'        => 'admin',
                'verified_badge'      => true,
                'verification_status' => 'approved',
                'is_blocked'          => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    public function down(): void
    {
        if ($email = env('ADMIN_EMAIL')) {
            DB::table('users')->where('email', $email)->delete();
        }
    }
};
