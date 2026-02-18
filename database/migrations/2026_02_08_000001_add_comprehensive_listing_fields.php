<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        // Step 1: Add new columns (only if they don't exist)
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'type_offre')) {
                $table->string('type_offre', 20)->default('negociable')->after('currency');
            }
            if (!Schema::hasColumn('listings', 'etat')) {
                $table->string('etat', 50)->default('bon_etat')->after('type_offre');
            }
            if (!Schema::hasColumn('listings', 'remarque_echange')) {
                $table->string('remarque_echange', 20)->nullable()->after('etat');
            }
            if (!Schema::hasColumn('listings', 'numero_whatsapp')) {
                $table->string('numero_whatsapp', 20)->nullable()->after('mediation_enabled');
            }
            if (!Schema::hasColumn('listings', 'numero_mobile')) {
                $table->string('numero_mobile', 20)->nullable()->after('numero_whatsapp');
            }
            if (!Schema::hasColumn('listings', 'contact_email')) {
                $table->string('contact_email', 255)->nullable()->after('numero_mobile');
            }
            if (!Schema::hasColumn('listings', 'visible_a')) {
                $table->string('visible_a', 255)->nullable()->after('wilaya');
            }
            if (!Schema::hasColumn('listings', 'pays')) {
                $table->string('pays', 100)->default('Algérie')->after('visible_a');
            }
        });

        // Add indexes (only if columns exist and indexes don't)
        try {
            Schema::table('listings', function (Blueprint $table) {
                $table->index('etat');
                $table->index('type_offre');
            });
        } catch (\Throwable $e) {
            // Indexes might already exist
        }

        // Step 2: Migrate negotiable -> type_offre (only if old column exists)
        if (Schema::hasColumn('listings', 'negotiable')) {
            DB::table('listings')->where('negotiable', true)->update(['type_offre' => 'negociable']);
            DB::table('listings')->where('negotiable', false)->update(['type_offre' => 'fix']);
        }

        // Step 3: Migrate condition -> etat (only if old column exists)
        if (Schema::hasColumn('listings', 'condition')) {
            DB::table('listings')->where('condition', 'new')->update(['etat' => 'jamais_utilise']);
            DB::table('listings')->where('condition', 'used')->update(['etat' => 'bon_etat']);
        }

        // Step 4: Restructure existing specs JSON
        $listings = DB::table('listings')->whereNotNull('specs')->get();
        foreach ($listings as $listing) {
            $oldSpecs = json_decode($listing->specs, true);
            if (!$oldSpecs || isset($oldSpecs['general'])) {
                continue; // Already structured or empty
            }

            $newSpecs = [];

            $generalKeys = ['brand' => 'fabricant', 'model' => 'modele', 'year' => 'annee_construction'];
            $motorKeys = ['engine_power' => 'puissance_par_moteur', 'fuel_type' => 'type_carburant', 'hours' => 'nombre_heures', 'power' => 'puissance_par_moteur', 'type' => 'propulsion'];
            $dimKeys = ['length' => 'longueur'];
            $otherKeys = ['hull_material' => 'hull_material', 'max_passengers' => 'max_passengers', 'seats' => 'max_passengers', 'part_number' => 'part_number', 'compatible_with' => 'compatible_with', 'part_type' => 'part_type', 'cylindree' => 'cylindree'];

            foreach ($oldSpecs as $key => $value) {
                if (isset($generalKeys[$key])) {
                    $newSpecs['general'][$generalKeys[$key]] = $value;
                } elseif (isset($motorKeys[$key])) {
                    $newSpecs['motorisation'][$motorKeys[$key]] = $value;
                } elseif (isset($dimKeys[$key])) {
                    $newSpecs['dimensions'][$dimKeys[$key]] = $value;
                } else {
                    $newSpecs['general'][$key] = $value;
                }
            }

            DB::table('listings')->where('id', $listing->id)->update([
                'specs' => json_encode($newSpecs),
            ]);
        }

        // Step 5: Drop old columns (only if they exist)
        if (DB::getDriverName() !== 'sqlite') {
            if (Schema::hasColumn('listings', 'negotiable')) {
                Schema::table('listings', function (Blueprint $table) {
                    $table->dropColumn('negotiable');
                });
            }
            if (Schema::hasColumn('listings', 'condition')) {
                Schema::table('listings', function (Blueprint $table) {
                    $table->dropColumn('condition');
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        if (!Schema::hasColumn('listings', 'negotiable')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->boolean('negotiable')->default(false)->after('price_dzd');
            });
        }
        if (!Schema::hasColumn('listings', 'condition')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->string('condition', 10)->default('used')->after('negotiable');
            });
        }

        if (Schema::hasColumn('listings', 'type_offre')) {
            DB::table('listings')->where('type_offre', 'negociable')->update(['negotiable' => true]);
            DB::table('listings')->whereIn('type_offre', ['fix', 'offert'])->update(['negotiable' => false]);
        }
        if (Schema::hasColumn('listings', 'etat')) {
            DB::table('listings')->where('etat', 'jamais_utilise')->update(['condition' => 'new']);
            DB::table('listings')->where('etat', '!=', 'jamais_utilise')->update(['condition' => 'used']);
        }

        $columnsToDrop = [];
        foreach (['type_offre', 'etat', 'remarque_echange', 'numero_whatsapp', 'numero_mobile', 'contact_email', 'visible_a', 'pays'] as $col) {
            if (Schema::hasColumn('listings', $col)) {
                $columnsToDrop[] = $col;
            }
        }

        if (!empty($columnsToDrop)) {
            Schema::table('listings', function (Blueprint $table) use ($columnsToDrop) {
                try {
                    $table->dropIndex(['etat']);
                    $table->dropIndex(['type_offre']);
                } catch (\Throwable $e) {}
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
