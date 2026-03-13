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

        // Create or update admin with new credentials
        $exists = DB::table('users')->where('email', 'Albabordz@gmail.com')->exists();

        if ($exists) {
            DB::table('users')
                ->where('email', 'Albabordz@gmail.com')
                ->update([
                    'password'            => Hash::make('BILALbilal16@'),
                    'account_type'        => 'admin',
                    'verified_badge'      => true,
                    'verification_status' => 'approved',
                    'is_blocked'          => false,
                    'updated_at'          => now(),
                ]);
        } else {
            DB::table('users')->insert([
                'name'                => 'Admin Albabor',
                'email'               => 'Albabordz@gmail.com',
                'phone'               => '0550000000',
                'password'            => Hash::make('BILALbilal16@'),
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
        DB::table('users')->where('email', 'Albabordz@gmail.com')->delete();
    }
};
