<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite does not support ALTER COLUMN; recreate via raw SQL
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::statement('
                CREATE TABLE payments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type TEXT NOT NULL CHECK (type IN (\'publish_listing\',\'featured_listing\',\'vendor_subscription\',\'mediation_fee\')),
                    amount_dzd INTEGER NOT NULL,
                    method TEXT NOT NULL CHECK (method IN (\'baridimob\',\'ccp\',\'bank_transfer\',\'paypal\',\'redotpay\')),
                    proof_path TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT \'pending\' CHECK (status IN (\'pending\',\'approved\',\'rejected\')),
                    rejection_reason TEXT,
                    listing_id INTEGER,
                    subscription_id INTEGER,
                    mediation_ticket_id INTEGER,
                    approved_by INTEGER,
                    approved_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL,
                    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
                    FOREIGN KEY (mediation_ticket_id) REFERENCES mediation_tickets(id) ON DELETE SET NULL,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ');

            DB::statement('INSERT INTO payments_new SELECT * FROM payments');
            DB::statement('DROP TABLE payments');
            DB::statement('ALTER TABLE payments_new RENAME TO payments');

            DB::statement('CREATE INDEX IF NOT EXISTS payments_status_type_index ON payments (status, type)');
            DB::statement('CREATE INDEX IF NOT EXISTS payments_user_id_index ON payments (user_id)');

            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // MySQL / PostgreSQL
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('method', ['baridimob', 'ccp', 'bank_transfer', 'paypal', 'redotpay'])->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::statement('
                CREATE TABLE payments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type TEXT NOT NULL CHECK (type IN (\'publish_listing\',\'featured_listing\',\'vendor_subscription\',\'mediation_fee\')),
                    amount_dzd INTEGER NOT NULL,
                    method TEXT NOT NULL CHECK (method IN (\'baridimob\',\'ccp\',\'bank_transfer\')),
                    proof_path TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT \'pending\' CHECK (status IN (\'pending\',\'approved\',\'rejected\')),
                    rejection_reason TEXT,
                    listing_id INTEGER,
                    subscription_id INTEGER,
                    mediation_ticket_id INTEGER,
                    approved_by INTEGER,
                    approved_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL,
                    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
                    FOREIGN KEY (mediation_ticket_id) REFERENCES mediation_tickets(id) ON DELETE SET NULL,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ');

            DB::statement('INSERT INTO payments_new SELECT * FROM payments WHERE method IN (\'baridimob\',\'ccp\',\'bank_transfer\')');
            DB::statement('DROP TABLE payments');
            DB::statement('ALTER TABLE payments_new RENAME TO payments');

            DB::statement('CREATE INDEX IF NOT EXISTS payments_status_type_index ON payments (status, type)');
            DB::statement('CREATE INDEX IF NOT EXISTS payments_user_id_index ON payments (user_id)');

            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('method', ['baridimob', 'ccp', 'bank_transfer'])->change();
            });
        }
    }
};
