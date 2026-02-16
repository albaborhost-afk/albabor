<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('listings', function (Blueprint $table) {
            $table->string('type_offre', 20)->default('negociable')->after('currency');
            $table->string('etat', 50)->default('bon_etat')->after('type_offre');
            $table->string('remarque_echange', 20)->nullable()->after('etat');
            $table->string('numero_whatsapp', 20)->nullable()->after('mediation_enabled');
            $table->string('numero_mobile', 20)->nullable()->after('numero_whatsapp');
            $table->string('contact_email', 255)->nullable()->after('numero_mobile');
            $table->string('visible_a', 255)->nullable()->after('wilaya');
            $table->string('pays', 100)->default('Algérie')->after('visible_a');

            $table->index('etat');
            $table->index('type_offre');
        });

        // Step 2: Migrate negotiable -> type_offre
        DB::table('listings')->where('negotiable', true)->update(['type_offre' => 'negociable']);
        DB::table('listings')->where('negotiable', false)->update(['type_offre' => 'fix']);

        // Step 3: Migrate condition -> etat
        DB::table('listings')->where('condition', 'new')->update(['etat' => 'jamais_utilise']);
        DB::table('listings')->where('condition', 'used')->update(['etat' => 'bon_etat']);

        // Step 4: Restructure existing specs JSON
        $listings = DB::table('listings')->whereNotNull('specs')->get();
        foreach ($listings as $listing) {
            $oldSpecs = json_decode($listing->specs, true);
            if (!$oldSpecs || isset($oldSpecs['general'])) {
                continue; // Already structured or empty
            }

            $newSpecs = [];

            // Map old flat keys to new nested structure
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

        // Step 5: Drop old columns (SQLite doesn't support dropColumn with enum easily, so we handle it)
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we can't easily drop enum columns, so we leave them
            // They won't be used anymore but won't cause issues
        } else {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn(['negotiable', 'condition']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('negotiable')->default(false)->after('price_dzd');
            $table->string('condition', 10)->default('used')->after('negotiable');
        });

        DB::table('listings')->where('type_offre', 'negociable')->update(['negotiable' => true]);
        DB::table('listings')->whereIn('type_offre', ['fix', 'offert'])->update(['negotiable' => false]);
        DB::table('listings')->where('etat', 'jamais_utilise')->update(['condition' => 'new']);
        DB::table('listings')->where('etat', '!=', 'jamais_utilise')->update(['condition' => 'used']);

        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['etat']);
            $table->dropIndex(['type_offre']);
            $table->dropColumn([
                'type_offre', 'etat', 'remarque_echange',
                'numero_whatsapp', 'numero_mobile', 'contact_email',
                'visible_a', 'pays',
            ]);
        });
    }
};
