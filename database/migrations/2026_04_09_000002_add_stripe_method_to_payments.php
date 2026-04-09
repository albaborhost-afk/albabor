<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::statement("
                CREATE TABLE payments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type TEXT NOT NULL CHECK (type IN ('publish_listing','featured_listing','vendor_subscription','mediation_fee')),
                    amount_dzd INTEGER NOT NULL,
                    method TEXT NOT NULL CHECK (method IN ('baridimob','ccp','bank_transfer','paypal','redotpay','card','stripe')),
                    proof_path TEXT,
                    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
                    rejection_reason TEXT,
                    listing_id INTEGER,
                    subscription_id INTEGER,
                    mediation_ticket_id INTEGER,
                    approved_by INTEGER,
                    approved_at DATETIME,
                    stripe_session_id TEXT,
                    stripe_payment_intent TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL,
                    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
                    FOREIGN KEY (mediation_ticket_id) REFERENCES mediation_tickets(id) ON DELETE SET NULL,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ");

            DB::statement('INSERT INTO payments_new SELECT * FROM payments');
            DB::statement('DROP TABLE payments');
            DB::statement('ALTER TABLE payments_new RENAME TO payments');

            DB::statement('CREATE INDEX IF NOT EXISTS payments_status_type_index ON payments (status, type)');
            DB::statement('CREATE INDEX IF NOT EXISTS payments_user_id_index ON payments (user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS payments_stripe_session_id_index ON payments (stripe_session_id)');

            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('baridimob','ccp','bank_transfer','paypal','redotpay','card','stripe') NOT NULL");
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
