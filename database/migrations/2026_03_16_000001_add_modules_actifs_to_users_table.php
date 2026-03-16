<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // MySQL ne supporte pas les valeurs par défaut sur les colonnes JSON
            // nullable + remplissage via le modèle
            $table->json('modules_actifs')->nullable()->after('actif');
        });

        // Initialiser les lignes existantes avec tous les modules activés
        DB::table('users')->whereNull('modules_actifs')->update([
            'modules_actifs' => '["accident","infraction","immigration"]',
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('modules_actifs');
        });
    }
};
